<?php
namespace Laravolt\Mural;

use Laravolt\Mural\Contracts\Commentable;

class Mural
{
    private $app;

    /**
     * Mural constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->config = config('mural');
    }

    public function render($content, $options = [])
    {
        $options = collect($options);
        $content = $this->getContentObject($content);
        $comments = $this->getComments($content);
        $totalComment = $content->comments()->count();
        $id = $content->getKey();
        $type = get_class($content);

        event('mural.render', [$content]);

        return view("mural::index", compact('content', 'id', 'type', 'comments', 'totalComment', 'options'))->render();
    }

    public function addComment($content, $body)
    {
        $author = auth()->user();
        $content = $this->getContentObject($content);
        $comment = $content->comments()->getRelated();
        $comment->body = $body;
        $comment->author()->associate($author);

        if($content->comments()->save($comment)) {
            event('mural.comment.add', [$comment, $content, $author]);
            return $comment;
        }

        return false;
    }

    public function getComments($content, $options = [])
    {
        $options = collect($options);
        $content = $this->getContentObject($content);
        $comments = $content->comments();

        $sorted = false;
        if($options->has('sort')) {
            if ($options->get('sort') == 'liked') {
                $comments->mostVoted();
                $sorted = true;
            }
        }

        if (!$sorted) {
            $comments->latest();
        }

        return $comments->paginate($this->config['per_page']);
    }

    public function remove($id)
    {
        $comment = Comment::find($id);
        $user = auth()->user();

        if($comment && $user->canModerateComment()) {
            $deleted = $comment->delete();

            if ($deleted) {
                event('mural.comment.remove', [$comment, $user]);
                return $comment;
            }
        }

        return false;
    }

    protected function getContentObject($content)
    {
        return $content;
    }

}
