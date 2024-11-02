<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{   
    // This function will show books listing page 
    public function index(){
        $books = Book::orderBy('created_at','DESC')->paginate(6); 
        
        return view('books.list',[
            'books' => $books
        ]);
    }

    // This method will show create book page
    public function create(){
        return view('books.create');
    }

    // This method will store created book on db
    public function store(Request $request){

        $rules =[
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'Status' => 'required',
        ];

        if (!empty($request->image)) {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()) {
            return redirect()->route('books.create')->withInput()->withErrors($validator);
        }

        // Save book in DB
        $book = new Book();
        $book->title = $request->title;
        $book->description = $request->description;
        $book->author = $request->author;
        $book->status  = $request->Status;
        $book->save();


        //Upload book image here
        if(!empty($request->image)) {
            
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext;
            $image->move(public_path('uploads/books'),$imageName);

            $book->image  = $imageName;
            $book->save();            

            $manager = new ImageManager(Driver::class);
            $img = $manager->read(public_path('uploads/books'.$imageName)); // 800 x 600

            $img->resize(990);
            $img->save(public_path('uploads/books/thumb'.$imageName));

        }

        return redirect()->route('books.index')->with('success','Books added Successfully.');        


    }
    
    // This method will edit book page
    public function edit(){

    }

    // This method will update book page
    public function update(){

    }

    // This method will delete book from db
    public function destroy(){

    }


}
