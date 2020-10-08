<?php

declare (strict_types=1);

namespace App\Http\Controllers;

use App\Book;
use App\BookReview;
use App\Http\Requests\PostBookRequest;
use App\Http\Requests\PostBookReviewRequest;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BooksController extends Controller
{
    public function getCollection(Request $request)
    {
        $input = $request->all();
        $book = Book::query();

        if (isset($input['title']))
            $book->where('title','like','%'.$input['title'].'%');

        if (isset($input['authors'])) {
            $authors = (strpos($input['authors'],',')!== false ) ? explode(',',$input['authors'] ) : [$input['authors']];
            $book->whereHas('authors', function($q)use ($authors){
                $q->whereIn('author_id', $authors );
            });

        }

        if (isset($input['sortColumn'])) {


            $sortDirection = (isset($input['sortDirection'])&& strtoupper( $input['sortDirection']) == 'DESC')?  'DESC': 'asc';


            if ($input['sortColumn']=='avg_review')
            {
                $book->withCount(['reviews as review_average' => function($query) {
                    $query->select(DB::raw('coalesce(avg(review),0)'));
                }])->orderBy('review_average',$sortDirection);

            }
            else {

                $book->OrderBy( $input['sortColumn'],$sortDirection );
            }
        }

        return BookResource::collection($book->paginate() );
    }

    public function post(PostBookRequest $request)
    {
        $input = $request->all();

        $book = new Book();
        $book->title =$input['title'];
        $book->isbn =$input['isbn'];
        $book->description =$input['description'];
        $book->save();

        $book->authors()->attach($input['authors']);


        return new BookResource($book);
    }

    public function postReview(Book $book, PostBookReviewRequest $request)
    {

        $bookReview = new BookReview();
        $bookReview->review = $request->input('review');
        $bookReview->comment = $request->input('comment');

        $bookReview->user_id = Auth::id();
        $bookReview->book_id = $book->id;

        $bookReview->save();;

        return new BookReviewResource($bookReview);
    }
}
