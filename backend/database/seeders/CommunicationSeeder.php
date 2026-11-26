<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\PathSeekerNotification;
use Illuminate\Database\Seeder;

class CommunicationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@pathseeker.test')->first();
        $users = User::whereIn('email', ['student@pathseeker.test', 'graduate@pathseeker.test', 'professional@pathseeker.test'])->get();
        if (! $admin || $users->isEmpty()) {
            return;
        }
        $samples = [['student@pathseeker.test', 'suggestion', 'Career Bank', 'Add more entry-level filters', 'It would help to filter careers by paths suitable for someone with no prior professional experience.', 'reviewing', 'Thanks for the thoughtful suggestion. Advanced experience filters are being prepared.'], ['graduate@pathseeker.test', 'question', 'Recommendations', 'How are match scores calculated?', 'I would like to understand which profile and quiz signals contribute most to each career match.', 'resolved', 'Open your Career DNA report to see quiz, interest, skill, and interaction factors separately.'], ['professional@pathseeker.test', 'bug', 'Saved Center', 'Note status was unclear', 'After saving a note I wanted a clearer confirmation that the private note had reached the server.', 'resolved', 'Saved Center now displays a confirmation after every successful private-note update.']];
        foreach ($samples as [$email,$type,$category,$subject,$message,$status,$response]) {
            $user = $users->firstWhere('email', $email);
            Feedback::updateOrCreate(['user_id' => $user->id, 'subject' => $subject], ['type' => $type, 'category' => $category, 'message' => $message, 'status' => $status, 'priority' => 'normal', 'admin_response' => $response, 'responded_by' => $admin->id, 'responded_at' => now()->subDays(2)]);
            if (! $user->notifications()->where('data', 'like', '%"seed_key":"feedback-demo"%')->exists()) {
                $user->notify(new PathSeekerNotification(['kind' => 'feedback_response', 'title' => 'Your feedback has a response', 'message' => $response, 'target' => '/app/feedback', 'seed_key' => 'feedback-demo']));
            }
        }
        $announcement = Announcement::updateOrCreate(['title' => 'Build evidence, not just plans'], ['created_by' => $admin->id, 'message' => 'Choose one saved career this week and complete a small project that demonstrates a required skill.', 'target_role' => 'everyone', 'is_published' => true, 'published_at' => now()->subDay()]);
        foreach ($users as $user) {
            if (! $user->notifications()->where('data', 'like', '%"seed_key":"announcement-demo"%')->exists()) {
                $user->notify(new PathSeekerNotification(['kind' => 'announcement', 'title' => $announcement->title, 'message' => $announcement->message, 'target' => '/app/notifications', 'announcement_id' => $announcement->id, 'seed_key' => 'announcement-demo']));
            }
        }
    }
}
