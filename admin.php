<?php

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/src/autoload.php';

require_role('admin');

$groupRepo = new GroupRepository($pdo);
$userRepo = new UserRepository($pdo);

$notice = '';
$error = '';
$old = [];

if (isset($_SESSION['admin_flash'])) {
    $notice = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'group') {
            $name = trim($_POST['group_name'] ?? '');

            if ($name === '' || mb_strlen($name) > 100) {
                throw new RuntimeException('Номи гурӯҳро дуруст ворид кунед.');
            }

            if ($groupRepo->existsByName($name)) {
                throw new RuntimeException('Ин ном аллакай вуҷуд дорад.');
            }

            $groupRepo->create($name);
            $_SESSION['admin_flash'] = 'Гурӯҳ бомуваффақият илова шуд.';

        } elseif ($action === 'user') {
            $old = $_POST;

            $name = trim($_POST['full_name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'student';
            $groupId = ($_POST['group_id'] ?? '') !== ''
                ? (int) $_POST['group_id']
                : null;
            $code = trim($_POST['student_code'] ?? '');

            if ($name === '' || mb_strlen($name) > 150) {
                throw new RuntimeException('Номи пурраи корбарро дуруст ворид кунед.');
            }

            if ($username === '' || mb_strlen($username) > 100) {
                throw new RuntimeException('Номи корбарро дуруст ворид кунед.');
            }

            if (strlen($password) < 8) {
                throw new RuntimeException('Парол бояд камаш 8 аломат дошта бошад.');
            }

            if (!in_array($role, ['teacher', 'student'], true)) {
                throw new RuntimeException('Нақши интихобшуда нодуруст аст.');
            }

            if ($userRepo->findByUsername($username)) {
                throw new RuntimeException('Ин номи корбар аллакай истифода мешавад.');
            }

            if ($role === 'student') {
                if ($code === '' || mb_strlen($code) > 50 || !$groupId) {
                    throw new RuntimeException('Рамзи донишҷӯ ва гурӯҳро ворид кунед.');
                }

                if (!$groupRepo->findById($groupId)) {
                    throw new RuntimeException('Гурӯҳи интихобшуда вуҷуд надорад.');
                }
            } else {
                $code = '';
                $groupId = null;
            }

            $userRepo->create([
                'full_name' => $name,
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'student_code' => $code,
                'group_id' => $groupId,
                'is_active' => 1,
            ]);

            $_SESSION['admin_flash'] = 'Корбар бомуваффақият илова шуд.';

        } elseif ($action === 'toggle_user') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id <= 0 || !$userRepo->toggleActive($id)) {
                throw new RuntimeException('Тағйир додани ҳолати корбар муваффақ нашуд.');
            }

            audit_log($pdo, 'user_status', 'Тағйир додани ҳолати корбар ' . $id);
            $_SESSION['admin_flash'] = 'Ҳолати корбар тағйир дода шуд.';

        } elseif ($action === 'delete_user') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new RuntimeException('Корбар интихоб нашудааст.');
            }

            $userRepo->delete($id);
            $_SESSION['admin_flash'] = 'Корбар нест карда шуд.';

        } elseif ($action === 'delete_group') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new RuntimeException('Гурӯҳ интихоб нашудааст.');
            }

            $groupRepo->delete($id);
            $_SESSION['admin_flash'] = 'Гурӯҳ нест карда шуд.';
        }

        if (isset($_SESSION['admin_flash'])) {
            $redirect = 'admin.php';

            if (!empty($_GET)) {
                $redirect .= '?' . http_build_query($_GET);
            }

            header('Location: ' . $redirect, true, 303);
            exit;
        }

    } catch (PDOException $ex) {
        http_response_code(422);
        $error = 'Ин маълумот такрорӣ аст ё бо сабтҳои дигар алоқаманд мебошад.';

    } catch (Throwable $ex) {
        http_response_code(422);
        $error = $ex->getMessage();
    }
}

/* Филтрҳо ва ҷустуҷӯ */
$q = trim($_GET['q'] ?? '');

$role = $_GET['role'] ?? '';
if (!in_array($role, ['student', 'teacher'], true)) {
    $role = '';
}

$groupId = null;
if (isset($_GET['group_id']) && ctype_digit((string) $_GET['group_id'])) {
    $value = (int) $_GET['group_id'];
    if ($value > 0) {
        $groupId = $value;
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;

$total = $userRepo->countSearch(
    $q,
    $role !== '' ? $role : null,
    $groupId
);

$totalPages = max(1, (int) ceil($total / $limit));
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;

$users = $userRepo->searchPaginated(
    $q,
    $role !== '' ? $role : null,
    $groupId,
    $limit,
    $offset
);

$groups = $groupRepo->getAll();

$pageTitle = 'Идоракунии корбарон';
require __DIR__ . '/partials/header.php';

$paginationParams = [
    'q' => $q,
    'role' => $role,
    'group_id' => $groupId,
];
$paginationParams = array_filter(
    $paginationParams,
    static fn($value) => $value !== null && $value !== ''
);
?>

<?php if ($notice): ?>
    <div class="alert alert-success"><?= e($notice) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="two-col">

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h3>Иловаи корбар</h3>
                <p>Донишҷӯ ё омӯзгорро сабт кунед.</p>
            </div>
            <i class="bi bi-person-plus"></i>
        </div>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="user">

            <label class="form-label">Номи насаб</label>
            <input class="form-control mb-3"
                   name="full_name"
                   maxlength="150"
                   value="<?= e($old['full_name'] ?? '') ?>"
                   required>

            <label class="form-label">Номи корбар</label>
            <input class="form-control mb-3"
                   name="username"
                   maxlength="100"
                   value="<?= e($old['username'] ?? '') ?>"
                   required>

            <label class="form-label">Парол (на камтар аз 8 аломат)</label>
            <input class="form-control mb-3"
                   type="password"
                   name="password"
                   minlength="8"
                   required>

            <label class="form-label">Нақш</label>
            <select class="form-select mb-3" name="role" id="roleSelect">
                <option value="student"
                    <?= ($old['role'] ?? 'student') === 'student' ? 'selected' : '' ?>>
                    Донишҷӯ
                </option>
                <option value="teacher"
                    <?= ($old['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>
                    Омӯзгор
                </option>
            </select>

            <div id="studentFields">
                <label class="form-label">Рамзи донишҷӯ</label>
                <input class="form-control mb-3"
                       name="student_code"
                       value="<?= e($old['student_code'] ?? '') ?>">

                <label class="form-label">Гурӯҳ</label>
                <select class="form-select mb-3" name="group_id">
                    <option value="">Интихоб кунед</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= (int) $g['id'] ?>"
                            <?= (string) ($old['group_id'] ?? '') === (string) $g['id'] ? 'selected' : '' ?>>
                            <?= e($g['group_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn btn-primary w-100">Сабт кардан</button>
        </form>
    </div>

    <div class="content-card">
        <div class="card-heading">
            <div>
                <h3>Иловаи гурӯҳ</h3>
                <p>Гурӯҳи нави таълимӣ созед.</p>
            </div>
            <i class="bi bi-diagram-3"></i>
        </div>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="group">

            <label class="form-label">Номи гурӯҳ</label>
            <input class="form-control mb-3"
                   name="group_name"
                   maxlength="100"
                   placeholder="Масалан: 1-400101"
                   required>

            <button class="btn btn-outline-primary w-100">
                Илова кардани гурӯҳ
            </button>
        </form>

        <hr>
        <h4 class="h6">Гурӯҳҳои мавҷуда</h4>

        <?php foreach ($groups as $g): ?>
            <div class="list-line">
                <span><?= e($g['group_name']) ?></span>

                <div class="d-flex gap-2">
                    <a class="icon-btn"
                       href="edit_group.php?id=<?= (int) $g['id'] ?>"
                       title="Таҳрир">
                        <i class="bi bi-pencil"></i>
                    </a>

                    <form method="post"
                          onsubmit="return confirm('Гурӯҳ нест карда шавад?')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete_group">
                        <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">

                        <button class="icon-btn text-danger" type="submit">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="content-card mt-4">
    <div class="section-heading">
        <div>
            <h3>Рӯйхати корбарон</h3>
            <p>Донишҷӯён ва омӯзгорони сабтшуда</p>
        </div>

        <span class="count-pill"><?= $total ?> корбар</span>
    </div>

    <!-- Ҷустуҷӯ ва филтр -->
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-4">
            <input class="form-control"
                   type="search"
                   name="q"
                   placeholder="Ҷустуҷӯ аз рӯйи ном..."
                   value="<?= e($q) ?>">
        </div>

        <div class="col-md-3">
            <select class="form-select" name="role">
                <option value="">Ҳамаи нақшҳо</option>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>
                    Донишҷӯ
                </option>
                <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>
                    Омӯзгор
                </option>
            </select>
        </div>

        <div class="col-md-3">
            <select class="form-select" name="group_id">
                <option value="">Ҳамаи гурӯҳҳо</option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?= (int) $g['id'] ?>"
                        <?= $groupId === (int) $g['id'] ? 'selected' : '' ?>>
                        <?= e($g['group_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-search"></i> Ҷустуҷӯ
            </button>
            <a href="admin.php" class="btn btn-outline-secondary">Тоза</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table modern-table">
            <thead>
                <tr>
                    <th>Номи насаб</th>
                    <th>Номи корбар</th>
                    <th>Нақш</th>
                    <th>Рамз / гурӯҳ</th>
                    <th>Ҳолат</th>
                    <th>Амал</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!$users): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            Корбар ёфт нашуд.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= e($u['full_name']) ?></strong></td>
                        <td><?= e($u['username']) ?></td>

                        <td>
                            <span class="role-pill">
                                <?= $u['role'] === 'teacher' ? 'Омӯзгор' : 'Донишҷӯ' ?>
                            </span>
                        </td>

                        <td>
                            <?= e($u['student_code'] ?? '') ?>
                            <?= $u['group_name'] ? ' / ' . e($u['group_name']) : '' ?>
                        </td>

                        <td>
                            <span class="status-pill <?= $u['is_active'] ? 'active' : 'closed' ?>">
                                <?= $u['is_active'] ? 'Фаъол' : 'Ғайрифаъол' ?>
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-1">
                                <form method="post">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="toggle_user">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">

                                    <button class="btn btn-sm btn-outline-secondary"
                                            type="submit"
                                            title="Тағйири ҳолат">
                                        <i class="bi bi-toggle-on"></i>
                                    </button>
                                </form>

                                <a class="btn btn-sm btn-outline-primary"
                                   href="edit_user.php?id=<?= (int) $u['id'] ?>"
                                   title="Таҳрир">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="post"
                                      onsubmit="return confirm('Корбар нест карда шавад?')">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">

                                    <button class="icon-btn text-danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Саҳифабандӣ -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Саҳифабандӣ">
            <ul class="pagination justify-content-center mt-3">

                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?<?= e(http_build_query($paginationParams + ['page' => max(1, $page - 1)])) ?>">
                        Қаблӣ
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?<?= e(http_build_query($paginationParams + ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?<?= e(http_build_query($paginationParams + ['page' => min($totalPages, $page + 1)])) ?>">
                        Баъдӣ
                    </a>
                </li>

            </ul>
        </nav>
    <?php endif; ?>

    <p class="text-muted small text-center">
        Саҳифаи <?= $page ?> аз <?= $totalPages ?> |
        Ҳамагӣ <?= $total ?> корбар
    </p>
</div>

<script>
const roleSelect = document.getElementById('roleSelect');
const studentFields = document.getElementById('studentFields');

function updateStudentFields() {
    studentFields.style.display =
        roleSelect.value === 'student' ? 'block' : 'none';
}

roleSelect.addEventListener('change', updateStudentFields);
updateStudentFields();
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
