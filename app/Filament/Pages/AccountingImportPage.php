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

    protected static ?string $navigationGroup = 'نظام المحاسبة والمالية';

    protected static ?string $navigationLabel = 'استيراد ملف Excel';

    protected static ?string $title = 'أداة استيراد ومطابقة ملف Excel المحاسبي';

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
            Notification::make()->title('يرجى اختيار ملف إكسل أولاً')->warning()->send();
            return;
        }

        $path = $this->uploadedFile->getRealPath();
        $service = new AccountingImportService();
        $this->previewData = $service->preview($path);
        $this->selectedSource = 'upload';

        Notification::make()->title('تمت قراءة ومعاينة الملف بنجاح')->success()->send();
    }

    public function executeImport(): void
    {
        $path = ($this->selectedSource === 'upload' && $this->uploadedFile)
            ? $this->uploadedFile->getRealPath()
            : base_path('database/data/Three_Digit_Software_Accounting_Dashboard.xlsx');

        if (!file_exists($path)) {
            Notification::make()->title('الملف غير موجود')->danger()->send();
            return;
        }

        $service = new AccountingImportService();
        $result = $service->import($path, Auth::id());
        $this->importResult = $result;

        // Refresh preview to show updated duplicate indicators
        $this->previewData = $service->preview($path);

        Notification::make()
            ->title('اكتملت عملية الاستيراد بنجاح')
            ->body("تم استيراد {$result['imported_rows']} حركة، وتعليق {$result['flagged_rows']} للمراجعة، وكشف {$result['duplicate_rows']} حركة مكررة تم تجنبها.")
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
