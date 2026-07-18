<?php

namespace App\Http\Controllers;

use App\Services\TeamChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeamChatController extends Controller
{
    public function __construct(private readonly TeamChatService $chat)
    {
    }

    public function index(Request $request): View
    {
        $membership = $this->chat->resolveMembership($request->user())
            ?? abort(403, 'Bạn chưa thuộc nhóm chat nội bộ nào.');

        $leader = $membership['leader'];
        $messages = $this->chat->messagesForTeam($leader);
        $participants = $this->chat->participants($leader);
        $canAnnounce = $this->chat->isTeamLeader($request->user());

        return view('employee.team-chat.index', [
            'leader' => $leader,
            'membership' => $membership,
            'messages' => $messages,
            'participants' => $participants,
            'canAnnounce' => $canAnnounce,
            'messagesRoute' => route('employee.team-chat.messages'),
            'storeRoute' => route('employee.team-chat.store'),
            'announceRoute' => $canAnnounce ? route('employee.team-chat.announce') : null,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $membership = $this->chat->resolveMembership($request->user())
            ?? abort(403);

        $afterId = $request->integer('after_id') ?: null;
        $messages = $this->chat->messagesForTeam($membership['leader'], $afterId);

        return response()->json([
            'messages' => $this->chat->serializeMessages($messages, (int) $membership['member']->id),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ], [
            'body.required' => 'Vui lòng nhập nội dung tin nhắn.',
        ]);

        try {
            $message = $this->chat->sendMessage($request->user(), $request->string('body')->toString());
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                throw $e;
            }

            return back()->withErrors($e->errors())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->chat->serializeMessages(collect([$message->load(['senderEmployee', 'senderUser'])]), (int) $this->chat->resolveMembership($request->user())['member']->id)[0],
            ]);
        }

        return back()->with('success', 'Đã gửi tin nhắn.');
    }

    public function announce(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'body.required' => 'Vui lòng nhập nội dung thông báo.',
        ]);

        try {
            $this->chat->sendAnnouncement(
                $request->user(),
                $request->string('title')->toString(),
                $request->string('body')->toString(),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Đã gửi thông báo nội bộ cho nhóm.');
    }
}
