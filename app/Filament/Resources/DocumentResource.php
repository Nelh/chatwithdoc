<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\ViewColumn;
use Carbon\Carbon;
use App\Filament\Resources\DocumentResource\Actions\ImportAction;

class DocumentResource extends Resource
{
    const DOCUMENT_TYPE = 'created';

    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(1)
                    ->schema([
                        ViewColumn::make('content')
                            ->view('components.preview-sm'),
                        Grid::make([
                            'default' => 1,
                        ])
                        ->extraAttributes(['class' => 'custom-section-style'])
                        ->schema([
                            Tables\Columns\TextColumn::make('title')
                                ->label('Title')
                                ->searchable()
                                ->sortable(),

                            Tables\Columns\TextColumn::make('updated_at')
                                ->dateTime()
                                ->sortable()
                                ->color('gray')
                                ->toggleable(isToggledHiddenByDefault: true),

                            Tables\Columns\TextColumn::make('expiration_date')
                                ->formatStateUsing(function ($state) {
                                    if (!$state) return null;

                                    $date = Carbon::parse($state);
                                    $isExpired = $date->isPast();

                                    return $isExpired
                                        ? 'Expired ' . $date->diffForHumans(['parts' => 2])
                                        : 'Expires in ' . now()->diffForHumans($date, [
                                            'parts' => 2,
                                            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE
                                        ]);
                                })
                                ->color(function ($state) {
                                    if (!$state) return null;
                                    return Carbon::parse($state)->isPast() ? 'danger' : 'gray';
                                })
                                ->sortable(),

                            Tables\Columns\TextColumn::make('processing_status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'completed' => 'success',
                                    'processing' => 'warning',
                                    'failed' => 'danger',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                ->icon(fn (string $state): string => match ($state) {
                                    'completed' => 'heroicon-o-check-circle',
                                    'processing' => 'heroicon-o-arrow-path',
                                    'failed' => 'heroicon-o-x-circle',
                                    default => 'heroicon-o-question-mark-circle',
                                })
                                ->visible(fn ($state) => $state != 'pending'),
                        ]),

                    ])
            ])
            ->filters([
                //
            ])
            ->contentGrid(['md' => 3, 'xl' => 4])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-equals')
                    ->url(fn (Document $record): string => DocumentResource::getUrl('document', ['record' => $record->uuid])),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->color('success')
                    ->icon('heroicon-o-folder-arrow-down')
                    ->action(fn (Document $record) => app('documentservice')->downloadDocumentService($record))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn (Document $record): string => DocumentResource::getUrl('document', ['record' => $record->uuid]))
            ->headerActions([
                ImportAction::make(),
                Tables\Actions\Action::make('New Document')
                    ->label('New Document')
                    ->icon('heroicon-o-plus')
                    ->action(function () {
                        if (auth()->user() && !auth()->user()->hasVerifiedEmail()) {
                            return redirect()->to(route('filament.app.resources.documents.index'))->with([
                                'email-verification' => 'You must first confirm your email address for full access to the system.'
                            ]);
                        }

                        $uuid = Str::uuid()->toString();

                        // Create a new document
                        $document = Document::create([
                            'title' => 'New Document',
                            'content' => null,
                            'template' => null,
                            'font' => null,
                            'type' => self::DOCUMENT_TYPE,
                            'uuid' => $uuid,
                            'user_id' => auth()->user()->id
                        ]);

                        return redirect(DocumentResource::getUrl('document', ['record' => $document->uuid]));
                    }),
            ])
            ->emptyStateIcon('heroicon-o-document')
            ->emptyStateHeading('No Documents')
            ->emptyStateDescription('All your Documents will show here.')
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'document' => Pages\Document::route('/{record}'),
        ];
    }
}
