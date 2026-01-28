<?php
declare(strict_types=1);

function auth_user(): ?array {
  session_start_safe();
  return $_SESSION['user'] ?? null;
}

function require_login(): array {
  $u = auth_user();
  if (!$u) redirect(base_path() . '/admin/login.php');
  return $u;
}

function is_super(array $u): bool {
  return ($u['role'] ?? '') === 'SUPER_ADMIN';
}

function dept_id(array $u): ?int {
  return isset($u['department_id']) ? (int)$u['department_id'] : null;
}

function can_manage_dept(array $u, ?int $departmentId): bool {
  if (is_super($u)) return true;
  if (($u['role'] ?? '') !== 'DEPT_ADMIN') return false;
  return $departmentId !== null && dept_id($u) === (int)$departmentId;
}

function scope_where_dept(array $u, string $col = 'department_id'): array {
  // returns [sql, params]
  if (is_super($u)) return ['1=1', []];
  return ["{$col} = :dept", [':dept' => dept_id($u)]];
}
