<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Exceptions\InvalidCommentException;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_comment(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $comment = Comment::createComment($ticket, $user, 'Test comment');

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test comment', $comment->comment);
        $this->assertEquals($user->id, $comment->user_id);
        $this->assertEquals($ticket->id, $comment->ticket_id);
        $this->assertFalse($comment->is_internal);
    }

    #[Test]
    public function it_throws_exception_for_empty_comment(): void
    {
        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage('Comment text cannot be empty');

        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        Comment::createComment($ticket, $user, '');
    }

    #[Test]
    public function it_throws_exception_for_whitespace_only_comment(): void
    {
        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage('Comment text cannot be empty');

        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        Comment::createComment($ticket, $user, '   ');
    }

    #[Test]
    public function it_can_create_internal_comment_as_builder(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();

        $comment = Comment::createComment($ticket, $builder, 'Internal note', true);

        $this->assertTrue($comment->is_internal);
    }

    #[Test]
    public function it_can_create_internal_comment_as_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $ticket = Ticket::factory()->create();

        $comment = Comment::createComment($ticket, $admin, 'Internal note', true);

        $this->assertTrue($comment->is_internal);
    }

    #[Test]
    public function it_prevents_homeowner_from_creating_internal_comment(): void
    {
        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage("User with role 'homeowner' cannot create internal comments");

        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();

        Comment::createComment($ticket, $homeowner, 'Trying to be internal', true);
    }

    #[Test]
    public function it_can_check_if_authored_by_user(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $comment = Comment::createComment($ticket, $author, 'Test comment');

        $this->assertTrue($comment->isAuthoredBy($author));
        $this->assertFalse($comment->isAuthoredBy($otherUser));
    }

    #[Test]
    public function it_allows_author_to_edit_comment(): void
    {
        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $this->assertTrue($comment->canBeEditedBy($author));
    }

    #[Test]
    public function it_allows_admin_to_edit_any_comment(): void
    {
        $author = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $this->assertTrue($comment->canBeEditedBy($admin));
    }

    #[Test]
    public function it_prevents_non_author_from_editing_comment(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $this->assertFalse($comment->canBeEditedBy($otherUser));
    }

    #[Test]
    public function it_can_update_comment_text(): void
    {
        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $comment->updateText('Updated text', $author);

        $this->assertEquals('Updated text', $comment->comment);
    }

    #[Test]
    public function it_throws_exception_when_non_author_updates_text(): void
    {
        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage('Only the comment author or an admin can edit this comment');

        $author = User::factory()->create();
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $comment->updateText('Hacked text', $otherUser);
    }

    #[Test]
    public function it_allows_admin_to_update_comment_text(): void
    {
        $author = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $comment->updateText('Admin edited text', $admin);

        $this->assertEquals('Admin edited text', $comment->comment);
    }

    #[Test]
    public function it_validates_comment_text_on_update(): void
    {
        $this->expectException(InvalidCommentException::class);

        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Original text');

        $comment->updateText('', $author);
    }

    #[Test]
    public function it_can_check_if_comment_is_internal(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();

        $internalComment = Comment::createComment($ticket, $builder, 'Internal', true);
        $publicComment = Comment::createComment($ticket, $builder, 'Public', false);

        $this->assertTrue($internalComment->isInternal());
        $this->assertFalse($publicComment->isInternal());
    }

    #[Test]
    public function it_can_check_if_comment_is_public(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();

        $internalComment = Comment::createComment($ticket, $builder, 'Internal', true);
        $publicComment = Comment::createComment($ticket, $builder, 'Public', false);

        $this->assertFalse($internalComment->isPublic());
        $this->assertTrue($publicComment->isPublic());
    }

    #[Test]
    public function it_can_mark_comment_as_internal(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $builder, 'Test', false);

        $comment->markAsInternal($builder);

        $this->assertTrue($comment->is_internal);
    }

    #[Test]
    public function it_prevents_homeowner_from_marking_as_internal(): void
    {
        $this->expectException(InvalidCommentException::class);

        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $homeowner, 'Test', false);

        $comment->markAsInternal($homeowner);
    }

    #[Test]
    public function it_can_mark_comment_as_public(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $builder, 'Test', true);

        $comment->markAsPublic($builder);

        $this->assertFalse($comment->is_internal);
    }

    #[Test]
    public function it_allows_builder_to_view_internal_comments(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $builder, 'Internal', true);

        $this->assertTrue($comment->canBeViewedBy($builder));
    }

    #[Test]
    public function it_allows_admin_to_view_internal_comments(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $builder, 'Internal', true);

        $this->assertTrue($comment->canBeViewedBy($admin));
    }

    #[Test]
    public function it_prevents_homeowner_from_viewing_internal_comments(): void
    {
        $builder = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $builder, 'Internal', true);

        $this->assertFalse($comment->canBeViewedBy($homeowner));
    }

    #[Test]
    public function it_allows_anyone_to_view_public_comments(): void
    {
        $author = User::factory()->create(['role' => UserRole::BUILDER]);
        $homeowner = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Public', false);

        $this->assertTrue($comment->canBeViewedBy($homeowner));
        $this->assertTrue($comment->canBeViewedBy($author));
    }

    #[Test]
    public function it_allows_author_to_delete_comment(): void
    {
        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Test');

        $this->assertTrue($comment->canBeDeletedBy($author));
    }

    #[Test]
    public function it_allows_admin_to_delete_any_comment(): void
    {
        $author = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Test');

        $this->assertTrue($comment->canBeDeletedBy($admin));
    }

    #[Test]
    public function it_prevents_non_author_from_deleting_comment(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Test');

        $this->assertFalse($comment->canBeDeletedBy($otherUser));
    }

    #[Test]
    public function it_ensures_user_can_delete_comment(): void
    {
        $author = User::factory()->create();
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Test');

        $comment->ensureCanBeDeletedBy($author);
        $this->assertTrue(true); // No exception thrown
    }

    #[Test]
    public function it_throws_exception_when_user_cannot_delete_comment(): void
    {
        $this->expectException(InvalidCommentException::class);
        $this->expectExceptionMessage('Only the comment author or an admin can delete this comment');

        $author = User::factory()->create();
        $otherUser = User::factory()->create(['role' => UserRole::HOMEOWNER]);
        $ticket = Ticket::factory()->create();
        $comment = Comment::createComment($ticket, $author, 'Test');

        $comment->ensureCanBeDeletedBy($otherUser);
    }
}
