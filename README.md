## Introduction

You are working on an online bookstore application. You have to finish a few web API methods.

The project contains functional tests. Your task is to make all the tests pass by writing the missing code.

### Setup

```
composer install
```

### Database seed

```
composer refresh-db
```

### Tests

```
composer test
```


## Requirements

The API has to provide endpoints for three operations:

**GET /api/books** - a list of Books with pagination, sorting and filtering options.

Available query parameters:\
`page` - page number \
`sortColumn` - one of `title` or `avg_review` \
`sortDirection` - one of `ASC` or `DESC` \
`title` - search by book title \
`authors` - search by author’s ID (comma-separated)


Sample response (HTTP 200)
```
{
   "data":[
      {
         "id":1,
         "isbn":"9077765476",
         "title":"Et hic et mollitia ea nihil culpa.",
         "description":"Possimus voluptatem rerum harum nemo asperiores. Consequuntur tenetur ut nemo ipsam placeat. Sunt eos cum assumenda quasi est. Dolores earum qui quod nihil commodi nisi.",
         "authors":[
            {
               "id":1,
               "name":"Dr. Beth Weber PhD",
               "surname":"Jenkins"
            }
         ],
         "review":{
            "avg":4,
            "count":3
         }
      }
   ],
   "links":{
      "first":"http:\/\/localhost\/api\/books?page=1",
      "last":"http:\/\/localhost\/api\/books?page=1",
      "prev":null,
      "next":null
   },
   "meta":{
      "current_page":1,
      "from":1,
      "last_page":1,
      "path":"http:\/\/localhost\/api\/books",
      "per_page":15,
      "to":5,
      "total":5
   }
}
```

TODO:
1. Implement `App\Http\Resources\BookResource::toArray` method.
2. Query the data from `Book` Eloquent model and respond with `BookResource` collection. 
3. Implement pagination feature (from Eloquent).
4. Allow sorting by title.
5. Allow sorting by average review.
6. Allow searching by title (SQL like query).
7. Allow searching by author’s ID.

---

**POST /api/books** - creates a new Book resource.

**_Access to this endpoint requires authentication with an API token and admin privileges._**

Required parameters:\
`isbn` - string (13 characters, digits only)\
`title` - string\
`description` - string\
`authors` - int[] - author’s ID


Sample response (HTTP 201)
```
{
   "data":{
      "id":1,
      "isbn":"9788328302341",
      "title":"Clean code",
      "description":"Lorem ipsum",
      "authors":[
         {
            "id":1,
            "name":"Prof. Darrin Mraz Jr.",
            "surname":"Bins"
         }
      ],
      "review":{
         "avg":0,
         "count":0
      }
   }
}
```

In case of validation errors, the API should respond with the default error list from the Laravel framework and the 422 HTTP code.

TODO:
1. Validate the required fields.
2. Ensure that the ISBN is unique and author’s ID exist in the DB.
3. Store Book in the DB.
4. Restrict access only for administrators with `auth.admin` middleware.
5. Respond with `BookResource`.

---

**POST /api/books/{id}/reviews** - creates a new BookReview resource.

**_Access to this endpoint requires authentication with an API token._**

Required parameters:\
`review` - int (1-10)\
`comment` - string


Sample response (HTTP 201)
```
{
   "data":{
      "id":1,
      "review":5,
      "comment":"Lorem ipsum",
      "user":{
         "id":1,
         "name":"Kody Lebsack"
      }
   }
}
```

In case of an invalid Book ID, the API should respond with the 404 HTTP code.\
In case of validation errors, the API should respond with the default error list from the Laravel framework and the 422 HTTP code.

TODO:
1. Validate the required fields.
2. Store BookReview in the DB.
3. Restrict access only for authenticated users.
4. Respond with `BookReviewResource`.

## Hints

1. The project is configured to use an SQLite database.
2. Do not modify any tests.
3. Look for comments with `@todo`.
