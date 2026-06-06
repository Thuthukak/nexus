<?php

declare(strict_types=1);

namespace Modules\HR\app\Console\Commands;

use App\Models\User;
use App\Notifications\DocumentExpiringNotification;
use Illuminate\Console\Command;
use Modules\HR\app\Models\HrDocument;
use Modules\HR\app\Services\HrSettingsService;

class ProcessDocumentExpiryCommand extends Command
{
    protected $signature   = 'hr:process-document-expiry';
    protected $description = 'Flag expired documents and notify admins of expiring documents';

    public function handle(HrSettingsService $settings): int
    {
        $warningDays = $settings->expiryWarningDays();
        $this->info("Warning window: {$warningDays} days");

        // Mark newly expired
        $expiredCount = 0;
        HrDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<', today())
            ->where('is_expired', false)
            ->each(function (HrDocument $doc) use (&$expiredCount) {
                $doc->update(['is_expired' => true]);
                $this->notifyAdmins($doc, isExpired: true);
                $expiredCount++;
            });

        // Notify expiring soon (once per document)
        $expiringSoonCount = 0;
        HrDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($warningDays))
            ->where('is_expired', false)
            ->where('expiry_notified', false)
            ->each(function (HrDocument $doc) use (&$expiringSoonCount) {
                $this->notifyAdmins($doc, isExpired: false);
                $doc->update(['expiry_notified' => true]);
                $expiringSoonCount++;
            });

        $this->info("Expired: {$expiredCount} | Expiring soon: {$expiringSoonCount}");

        return Command::SUCCESS;
    }

    private function notifyAdmins(HrDocument $doc, bool $isExpired): void
    {
        $notification = new DocumentExpiringNotification($doc, $isExpired);

        User::role(['Super Admin', 'Admin'])
            ->each(fn ($user) => $user->notify($notification));
    }
}
