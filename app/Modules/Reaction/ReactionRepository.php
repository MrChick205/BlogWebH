<?php
namespace App\Modules\Reaction;

class ReactionRepository
{
    public function findByPostAndUser($postId, $userId)
    {
        return Reaction::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByPost($postId, $perPage = 20)
    {
        return Reaction::with(['user'])
            ->where('post_id', $postId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return Reaction::create($data);
    }

    public function update($id, array $data)
    {
        $reaction = Reaction::find($id);

        if ($reaction) {
            $reaction->update($data);
        }

        return $reaction;
    }

    public function delete($id)
    {
        return Reaction::destroy($id);
    }
}