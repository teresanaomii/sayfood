<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestaurantStoreUpdateRequest;
use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function show($id)
    {
        $restaurant = Restaurant::with('foods')->findOrFail($id);
        $mainCourses = Food::where('restaurant_id', $id)->where('category_id', 1)->get();
        $desserts    = Food::where('restaurant_id', $id)->where('category_id', 2)->get();
        $drinks      = Food::where('restaurant_id', $id)->where('category_id', 3)->get();
        $snacks      = Food::where('restaurant_id', $id)->where('category_id', 4)->get();
        return view('restaurantmenu-customer', compact('restaurant', 'mainCourses', 'desserts', 'snacks', 'drinks'));
    }

    public function manageFood()
    {
        $restaurantId = Auth::user()->restaurant->id;
        $restaurant = Restaurant::with('foods')->findOrFail($restaurantId);
        $foods = Food::where('restaurant_id', $restaurantId)->get();
        $categories = Category::all();

        return view('restaurant-foods', compact('restaurant', 'foods', 'categories'));
    }

    public function store(RestaurantStoreUpdateRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $restaurantId = Auth::user()->restaurant->id;

            $expDatetime = Carbon::parse($validatedData['exp_date'] . ' ' . $validatedData['exp_time']);
            $status = $request->has('status') ? 'Available' : 'Out of Stock';

            $photoPath = null;
            if ($request->hasFile('image_url')) {
                $restaurantName = Auth::user()->restaurant->name;
                $folderName = Str::slug($restaurantName, '_');
                $path = 'food_images/' . $folderName;
                $photoPath = $request->file('image_url')->store($path, 'public');
            }

            Food::create([
                'restaurant_id' => $restaurantId,
                'name' => $validatedData['name'],
                'category_id' => $validatedData['category_id'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'exp_datetime' => $expDatetime,
                'image_url' => $photoPath,
                'stock' => $validatedData['stock'],
                'status' => $status,
            ]);

            return redirect()->back()->with('status', 'Food item has been added successfully!');
        } catch (\Exception $e) {
            Log::error('Food creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while adding the food item. Please try again.'])
                ->withInput()
                ->with('show_modal', 'addFoodModal'); 
        }
    }

    public function update(RestaurantStoreUpdateRequest $request, $id)
    {
        try {
            $food = Food::findOrFail($id);
            $validatedData = $request->validated();

            $expDatetime = Carbon::parse($validatedData['exp_date'] . ' ' . $validatedData['exp_time']);
            $status = $request->has('status') ? 'Available' : 'Out of Stock';

            $photoPath = $food->image_url;
            if ($request->hasFile('image_url')) {
                if ($food->image_url) {
                    Storage::disk('public')->delete($food->image_url);
                }

                $restaurantName = $food->restaurant->name;
                $folderName = Str::slug($restaurantName, '_');
                $path = 'food_images/' . $folderName;
                $photoPath = $request->file('image_url')->store($path, 'public');
            }

            $food->update([
                'name' => $validatedData['name'],
                'category_id' => $validatedData['category_id'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'exp_datetime' => $expDatetime,
                'image_url' => $photoPath,
                'stock' => $validatedData['stock'],
                'status' => $status,
            ]);

            return redirect()->back()->with('status', 'Food item has been updated successfully!');
        } catch (\Exception $e) {
            Log::error('Food update failed for food ID ' . $id . ': ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while updating the food item. Please try again.'])
                ->withInput()
                ->with('show_modal', 'editFoodModal');
        }
    }

    public function destroy($id)
    {
        $food = Food::findOrFail($id);

        // if ($food->image_url) {
        //     Storage::disk('public')->delete($food->image_url);
        // }

        $food->delete();

        return redirect()->route('manage.food.restaurant')->with('status', 'Food item has been deleted successfully!');
    }
}
