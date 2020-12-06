<?php

namespace App\Http\Controllers;

use App\FlowerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function checkAccount() {
        if(auth()->user()->role_id != 1) return redirect()->route('home')->with('status','[err] Please login with manager account');
    }

    public function viewCategories() {
        if($this->checkAccount()) return $this->checkAccount();

        $categories = FlowerCategory::orderBy('category_name')->get();

        return view('manager.manage-categories',['categories'=>$categories]);
    }

    public function deleteCategory(Request $request) {
        if($this->checkAccount()) return $this->checkAccount();

        $data = FlowerCategory::find($request->id)->first();
        $data->delete();
        return back()->with('status','[scc] Success delete category');
    }

    public function viewCategory($id) {
        if($this->checkAccount()) return $this->checkAccount();

        $category = FlowerCategory::where('id',$id)->first();
        return view('manager.update-category',['category'=>$category]);
    }

    //Update Categories Form
    public function updateFormCategories($id){
        $category = FlowerCategory::where('id',$id)->first();
        return view('updateCategory',['category'=>$category]);
    }

    //Update Categories
    public function updateCategory(Request $request, $id) {
        $message = [
            'category_name.min'         => 'New category name with minimum 5 characters',
            'category_name.required'    => 'New category name must be filled',
        ];	
        
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|min:5'
        ], $message);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput($request->all);
        }

        $category                = FlowerCategory::find($id);
        $category->category_name = $request->category_name;

        if($request->file('category_img') != null)
        {
            $file = $request->file('category_img');
            $destinationPath = 'storage\app\public\assets';
            $filename = date('YmdHis')."_"."Category".$request->category_name.".".$file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);

            $category->category_image = $destinationPath.$filename;
    	}

        $category->save();

        return redirect()->route('updateFormCategories', [$id]);
        // return redirect()->back()->with(['status' => 'Profile updated successfully.']);
    }
}
