<?php
namespace App\Modules\Comment;

class CommentRepository
{
    public function all()
    {
        return Comment::with(['user', 'post'])->get();
    }

    public function find($id)
    {
        return Comment::with(['user', 'post'])->find($id);
    }

    public function findByPost($postId, $perPage = 20)
    {
        return Comment::with(['user', 'post'])
            ->where('post_id', $postId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return Comment::create($data);
    }

    public function update($id, array $data)
    {
        $comment = $this->find($id);
        if ($comment) {
            $comment->update($data);
        }
        return $comment;
    }

    public function delete($id)
    {
        return Comment::destroy($id);
    }
}