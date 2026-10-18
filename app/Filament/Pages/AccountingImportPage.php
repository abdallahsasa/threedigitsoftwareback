<?php

namespace App\Filament\Pages;

use App\Models\AccountingImportBatch;
use App\Services\AccountingImportService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class AccountingImportPage extends Page implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.pages.accounting-import-page';

    protected static ?string $navigationGroup = 'Accounting & Finance';

    protected static ?string $navigationLabel = 'Excel Importer';

    protected static ?string $title = 'Excel Importer & Ledger Synchronization';

    protected static ?int $navigationSort = 7;

    public $uploadedFile;
    public ?array $previewData = null;
    public ?array $importResult = null;
    public string $selectedSource = 'system_file'; // 'system_file' or 'upload'

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->canCreateTransactions());
    }

    public function mount(): void
    {
        // Auto preview bundled system file on load
        $this->loadSystemFilePreview();
    }

    public function loadSystemFilePreview(): void
    {
        $path = base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');
        if (file_exists($path)) {
            $service = new AccountingImportService();
            $this->previewData = $service->preview($path);
            $this->selectedSource = 'system_file';
        }
    }

    public function handleFileUpload(): void
    {
        if (!$this->uploadedFile) {
            Notification::make()->title('Please choose an Excel (.xlsx) file first')->warning()->send();
            return;
        }

        $path = $this->uploadedFile->getRealPath();
        $service = new AccountingImportService();
        $this->previewData = $service->preview($path);
        $this->selectedSource = 'upload';

        Notification::make()->title('Excel file parsed and preview generated successfully')->success()->send();
    }

    public function executeImport(): void
    {
        $path = ($this->selectedSource === 'upload' && $this->uploadedFile)
            ? $this->uploadedFile->getRealPath()
            : base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');

        if (!file_exists($path)) {
            Notification::make()->title('Target file not found')->danger()->send();
            return;
        }

        $service = new AccountingImportService();
        $result = $service->import($path, Auth::id());
        $this->importResult = $result;

        // Refresh preview to show updated duplicate indicators
        $this->previewData = $service->preview($path);

        Notification::make()
            ->title('Excel Import Completed Successfully')
            ->body("Imported {$result['imported_rows']} records, staged {$result['flagged_rows']} in review queue, and prevented {$result['duplicate_rows']} duplicate transactions.")
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        $batches = AccountingImportBatch::with('importer')
            ->latest()
            ->take(5)
            ->get();

        return [
            'batches' => $batches,
            'previewData' => $this->previewData,
            'importResult' => $this->importResult,
        ];
    }
}
