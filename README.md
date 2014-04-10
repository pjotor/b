/b
==

## Persistant KeyValue storage
-------

Some times you just need a quick and dirty data storage, something that just
gives you a bucket to pour some data into.

_This is that bucket._


# Saving
-------

Saving is as simple as a `GET` request to the server. If an ID is supplied
that post will be updated.

_Remember that this method limits the amount of data transported to the max
URL length, so try to keep it under 1855 characters in total._

## Save (jQuery)

    function saveData(key, data, id){
        var request = {
            key: key,
            data: JSON.stringify(data)
        };
        
        if(id)
            request.id = id;
        
        $.getJSON("/b", request)
        .fail(function(re){ 
            //Handle failures to save here
        })
        .done(function(re){
            //Handle successful save here
        })
    }
        


If the save is successful you will get a JSON object back consisting of these
keys:

  * `type` Response type (ie. _"success"_) 
  * `info` Action performed (ie. _"inserted"_) 
  * `id` The ID of the element effected 

## Save response example

    {
        type: "success", 
        info: "inserted", 
        id: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqr"
    }
        


# Loading
-------

Loading is just as simple, just supply the key of the bucket and the ID of the
data.

## Load (jQuery)

    function loadData(key, id){
        $.getJSON("/b",{
            key: key,
            id: id
        })
        .fail(function(re){ 
            //Handle load fail here
        })
        .done(function(re){
            //Handle successful loading here
            //Don't forgett to parse it if it's JSON
            //i.e. JSON.parse(re)
        })
    }
        


Successfully loading data returns these keys:

  * `type` Response type (ie. _"success"_) 
  * `info` Action performed (ie. _"loaded"_) 
  * `id` The ID of the element effected 
  * `data` The data saved (as a string) 
  * `created` The time the ID was created 

## Load response example
        
    {
        type: "success", 
        info: "loaded", 
        id: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqr",
        data: '{"hello":"world"}', 
        created: "1970-01-01 00:00:00"
    }
        


# Errors
-------

Not often but sometimes you go goof up, then the server will return an error.
This happens when you don't send enough parameters, the wrong parameters or
something else is wrong. This is what it can look like:

## Error response example
    
    {
        "type":"error",
        "info":"wrong parameters"
    }  
        


_Please note that all errors will be wrapped in a HTTP 500 response._


That's it. ♥

