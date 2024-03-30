<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Group::make()->schema([
                    Section::make('Product Information')->schema([

                        TextInput::make('name')
                         ->required()
                         ->maxLength(255)
                         ->live(onBlur: true)
                         ->afterStateUpdated(function (string $operation, $state, Set $set){
                            if($operation !== 'create'){
                                return;
                            }
                            $set('slug', Str::slug($state));
                         }),

                        TextInput::make('slug')
                         ->required()
                         ->maxLength(255)
                         ->disabled()
                         ->dehydrated()
                         ->unique(Product::class, 'slug', ignoreRecord: true),

                        MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->fileAttachmentsDirectory('product')

                    ])->columns(2),

                    // xử lí ảnh tại đây
                    Section::make('Images')->schema([
                        FileUpload::make('images')
                        ->multiple()
                        ->directory('products')
                        ->maxFiles(5)
                        ->reorderable()
                    ])
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Price')->schema([
                        TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->prefix('USD')
                    ]),

                    Section::make('Associations')->schema([
                        Select::make('category_id')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'name'),

                        Select::make('brand_id')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('brand', 'name')
                    ]),

                    Section::make('Status')->schema([
                        Toggle::make('ins_stock')
                        ->required()
                        ->default(true),

                        Toggle::make('is_active')
                        ->required()
                        ->default(true),

                        Toggle::make('is_featured')
                        ->required(),

                        Toggle::make('on_sale')
                        ->required(),
                    ])

                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')
                  ->searchable(),

                TextColumn::make('category.name')
                  ->sortable(),

                TextColumn::make('brand.name')
                  ->sortable(),

                TextColumn::make('price')
                  ->money('USD')
                  ->sortable(),

                IconColumn::make('is_featured')
                  ->boolean(),

                IconColumn::make('on_sale')
                  ->boolean(),

                IconColumn::make('in_stock')
                  ->boolean(),

                IconColumn::make('is_active')
                  ->boolean(),

                TextColumn::make('created_at')
                  ->dateTime()
                  ->sortable()
                  ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                  ->dateTime()
                  ->sortable()
                  ->toggleable(isToggledHiddenByDefault: true)

            ])
            ->filters([
                //tìm kiếm theo chủng loại
                SelectFilter::make('category')
                   ->relationship('category', 'name'),
                SelectFilter::make('brand')
                   ->relationship('brand', 'name'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),

                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
