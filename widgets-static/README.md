# Widget templates 
Static templates for Projectaanvraag (Cultuurnet)


## Documentation

 * install Gulp and Node (> v6)
 * bower install 
 * npm install
 * use nvm for versioning
 
 
 ### GULP tasks to know about
 
  * **gulp serve**:  your usual serve task
  * **gulp build**: building to [dist](/dist)
  * **gulp styles**: building styles from [widgets-static/app/styles](widgets-static/app/styles) to [web/assets/css](web/assets/css)

#### Running into issues running GULP tasks

If you run into an error code similar to:
```
../src/node_contextify.cc:676:static void node::contextify::ContextifyScript::New(const FunctionCallbackInfo<v8::Value> &): Assertion `args[1]->IsString()' failed.
```

[The following solution](https://github.com/gulpjs/gulp/issues/2162#issuecomment-384506747) might help you
   
   
#### Testing components 

 * start server : `gulp serve`
 * go to [http://localhost:12000/fulltest/](/http://localhost:12000/fulltest/)
 * choose template and add components to the regions
 
 
  
 