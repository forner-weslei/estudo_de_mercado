<?php

namespace App\Policies;

use App\Models\Study;
use App\Models\User;

class StudyPolicy
{
    public function view(User $user, Study $study): bool { return $study->user_id === $user->id; }
    public function update(User $user, Study $study): bool { return $study->user_id === $user->id; }
    public function delete(User $user, Study $study): bool { return $study->user_id === $user->id; }
}
