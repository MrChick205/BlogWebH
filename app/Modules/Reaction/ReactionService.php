<?php
namespace App\Modules\Reaction;

class ReactionService
{
    protected $reactionRepository;

    public function __construct(ReactionRepository $reactionRepository)
    {
        $this->reactionRepository = $reactionRepository;
    }

    public function findByPost($postId, $perPage = 20)
    {
        return $this->reactionRepository->findByPost($postId, $perPage);
    }

    public function react($postId, $userId, $type)
    {
        $reaction = $this->reactionRepository
            ->findByPostAndUser($postId, $userId);

        if ($reaction) {
            if ($reaction->type === $type) {
                $this->reactionRepository->delete($reaction->id);
                return ['message' => 'Reaction removed'];
            }

            return $this->reactionRepository->update($reaction->id, [
                'type' => $type
            ]);
        }

        return $this->reactionRepository->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'type' => $type,
        ]);
    }

    public function unreact($postId, $userId)
    {
        $reaction = $this->reactionRepository
            ->findByPostAndUser($postId, $userId);

        if ($reaction) {
            $this->reactionRepository->delete($reaction->id);
        }

        return ['message' => 'Reaction removed'];
    }
}