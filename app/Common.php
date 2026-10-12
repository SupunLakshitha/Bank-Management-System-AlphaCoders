<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('session_branch_id')) {
    /**
     * Branch stored for this login. Older sessions may not have it yet, so it is
     * read from the user record and saved for the rest of the session.
     */
    function session_branch_id(): ?int
    {
        static $resolved = false;
        static $branchId = null;

        if ($resolved) {
            return $branchId;
        }
        $resolved = true;

        $stored = session()->get('branch_id');
        if ($stored !== null && $stored !== '' && (int) $stored > 0) {
            $branchId = (int) $stored;

            return $branchId;
        }

        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return null;
        }

        $user = db_connect()->table('users')->select('branch_id')->where('id', $userId)->get()->getRowArray();
        $found = (int) ($user['branch_id'] ?? 0);
        if ($found <= 0) {
            return null;
        }

        $branch = db_connect()->table('branches')->select('name')->where('id', $found)->get()->getRowArray();
        session()->set([
            'branch_id'   => $found,
            'branch_name' => $branch['name'] ?? null,
        ]);

        $branchId = $found;

        return $branchId;
    }
}

if (! function_exists('user_can_access_branch')) {
    /**
     * Super admins work across every branch.
     * Every other signed-in user is limited to the branch stored on their user record.
     */
    function user_can_access_branch(?int $branchId): bool
    {
        if (session()->get('role') === 'SUPER ADMIN') {
            return true;
        }

        $ownBranchId = session_branch_id();

        return $ownBranchId !== null && (int) $branchId === $ownBranchId;
    }
}
