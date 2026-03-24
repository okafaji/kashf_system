<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class EnforceSingleRolePerUser extends Command
{
    protected $signature = 'users:enforce-single-role
                            {--apply : Apply changes to database}
                            {--chunk=200 : Number of users per chunk}';

    protected $description = 'Ensure each user has at most one role based on configured role priority';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $priority = collect(array_keys(config('permissions.roles', [])))
            ->values()
            ->flip()
            ->map(fn($index) => (int) $index)
            ->all();

        $totalUsers = 0;
        $usersWithMultipleRoles = 0;
        $updatedUsers = 0;
        $rows = [];

        User::query()
            ->with('roles')
            ->orderBy('id')
            ->chunk($chunkSize, function ($users) use (
                &$totalUsers,
                &$usersWithMultipleRoles,
                &$updatedUsers,
                &$rows,
                $priority,
                $apply
            ) {
                foreach ($users as $user) {
                    $totalUsers++;

                    $roles = $user->roles;
                    if ($roles->count() <= 1) {
                        continue;
                    }

                    $usersWithMultipleRoles++;

                    $selectedRole = $roles
                        ->sortBy(function ($role) use ($priority) {
                            $weight = $priority[$role->name] ?? 99999999;
                            return sprintf('%08d-%08d', $weight, (int) $role->id);
                        })
                        ->first();

                    if (!$selectedRole) {
                        continue;
                    }

                    $allRoleNames = $roles->pluck('name')->values();
                    $removedRoleNames = $allRoleNames
                        ->reject(fn($name) => $name === $selectedRole->name)
                        ->values()
                        ->all();

                    $rows[] = [
                        (string) $user->id,
                        $user->name,
                        $allRoleNames->implode(', '),
                        $selectedRole->name,
                        implode(', ', $removedRoleNames),
                    ];

                    if ($apply) {
                        $user->syncRoles([$selectedRole]);
                        $updatedUsers++;
                    }
                }
            });

        $this->newLine();
        $this->info('========================================');
        $this->info('Single Role Enforcement Report');
        $this->info('========================================');
        $this->line('Total users checked: ' . $totalUsers);
        $this->line('Users with multiple roles: ' . $usersWithMultipleRoles);
        $this->line('Users updated: ' . $updatedUsers);

        if (!$apply) {
            $this->warn('Mode: DRY RUN (no database changes)');
            $this->line('Run again with --apply to persist changes.');
        } else {
            $this->info('Mode: APPLY (changes saved to database)');
        }

        if (!empty($rows)) {
            $this->newLine();
            $this->line('Affected users preview (up to 50 rows):');
            $this->table(
                ['User ID', 'User Name', 'Current Roles', 'Kept Role', 'Removed Roles'],
                array_slice($rows, 0, 50)
            );

            if (count($rows) > 50) {
                $this->line('... and ' . (count($rows) - 50) . ' more rows.');
            }
        }

        $this->info('========================================');

        return 0;
    }
}
