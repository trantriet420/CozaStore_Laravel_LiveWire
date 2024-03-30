<?php

namespace App\Livewire;

use App\Livewire\Partials\Navbar;
use App\Helpers\CartManagement;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

#[Title('Product Detail - CozaStore')]
class ProdutDetailPage extends Component
{
    use LivewireAlert;
    public $slug;

    public $quantity = 1;

    public function mount($slug){
        $this->slug = $slug;
    }

    public function increaseQty(){
        $this->quantity++;
    }

    public function decreaseQty(){
        if($this->quantity > 1){
            $this->quantity--;
        }
    }

    //add to cart method
    public function addToCart($prosuct_id){
        $total_count = CartManagement::addItemToCartWithQty($prosuct_id, $this->quantity);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Added product success!', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }
    public function render()
    {
        return view('livewire.produt-detail-page',[
            'product' => Product::where('slug',$this->slug)->firstOrFail(),
        ]);
    }
}
