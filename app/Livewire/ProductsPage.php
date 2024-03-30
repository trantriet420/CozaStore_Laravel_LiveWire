<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
// use CartManagement;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Product - CozaStore')]
class ProductsPage extends Component
{
    use LivewireAlert;
    use WithPagination;

    #[Url]
    public $selected_categories= [];

    #[Url]
    public $selected_brands = [];

    #[Url]
    public $featured;

     #[Url]
    public $on_sale;

    #[Url]
    public $price_range=200000;

    #[Url]
    public $sort = 'latest';

    //add to cart method
    public function addToCart($prosuct_id){
        $total_count = CartManagement::addItemToCart($prosuct_id);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Added product success!', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function render()
    {
        $product = Product::query()->where('is_active',1);

        if(!empty($this->selected_categories)){
             $product->whereIn('category_id', $this->selected_categories);
        }

        if(!empty($this->selected_brands)){
             $product->whereIn('brand_id', $this->selected_brands);
        }

        if($this->featured){
            $product->where('is_featured', 1);
        }

        if($this->on_sale){
            $product->where('on_sale', 1);
        }

        if($this->price_range){
            $product->whereBetween('price',[0,$this->price_range]);
        }

        if($this->sort == 'latest'){
            $product->latest();
        }

        if($this->sort == 'price'){
            $product->orderBy('price');
        }

        return view('livewire.products-page',[
            'products' => $product->paginate(3),
            'brands' => Brand::query()->where('is_active',1)->get(['id','name','slug']),
            'categories' => Category::query()->where('is_active',1)->get(['id','name','slug']),
        ]);
    }
}
