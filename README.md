# Cangur.cat (Laravel)

### Video: https://youtu.be/Z2DeEFUruGc
(Sorry for the quality of the video and the sound; I could only film it on a 2015 MacBook.) (Also, I didn't have time to show the teachers CRUD or other features like that teachers answers appear first.)

Cangur.cat is a web application for preparation for the Catalonian maths competition Copa Cangur, with authentication, questions/problems CRUD, answers CRUD, and some other features such as student and teacher roles or different response types that arise from the structure of the project and the needs of the users.

This web application provides an alternative to the system (Moodle) used in my high school for preparation for this competition, allowing much more customization than Moodle and providing the basic functionalities needed. I am sure that I will continue developing this web application, 
adding more features such as a blog and question classification. In fact, I already did this and uploaded it to my dad’s server, so it is currently online at cangur.cat. I do not submit that version because it was partially implemented with AI assistance, which made it harder to reason about while learning and introduced unnecessary complexity.

For this submission, I rolled back, completed a course by Jeffrey Way from Laracasts (“30 Days to Laravel”), and recreated the project without relying on vibe-coding, focusing instead on understanding each part of the framework.

The main decision I made at the beginning of this project was choosing which language or framework to use. Although in CS50 we already went a bit beyond Python and Flask, I decided to choose Laravel for two reasons.
First, because it was the language my dad used to program in, and I wanted to learn it to be able to understand and help with his projects. (He now mostly uses Python, partly due to the rise of AI tools, but I do not regret this choice because I learned a lot from a framework that is still very relevant.)
Second, because Professor David Malan and the final project instructions explicitly encouraged us to learn new languages independently, now that CS50 had taught us a solid methodology.

For the rest of the decisions, I always looked for the most conventional and simple approach. For example, when deciding how to implement components and layouts, I followed what seemed to me the simplest and most consistent solution (using <x-layouts.app>), since it works the same way for both layouts and components. Similarly, 
when deciding how to implement authorization, I followed Laravel conventions and implemented policies.


## Quick Start (what we’ll do)

1) Install dependencies (Composer for PHP, npm for assets).  
2) Configure your .env (database settings).  
3) Run the database migrations (this creates the tables).  
4) Start the dev servers (PHP + Vite) and open the site.  
5) Create your first user and make it a teacher (so you see the edit tools).


## Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+ and npm
- A database (MySQL or MariaDB is typical; SQLite can also work for quick tests)

Tip: On macOS/Linux you can install PHP and Composer with your package manager. On Windows, try Laragon or WAMP. If you already develop Laravel projects, you likely have all of this.


## Installation

Once you downloaded the project, install dependencies:

```bash
# 1) PHP deps
composer install

# 2) Node deps (for styles/JS during development)
npm install
```

Create your .env and app key:

```bash
# 4) Copy example env and generate APP_KEY (used to encrypt sessions, etc.)
cp .env.example .env
php artisan key:generate
```

Configure the database in .env (open .env in your editor and fill these lines):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cangur_db
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

- Use a real database and user here. If the database doesn’t exist yet, create it (you can do this in your MySQL client or GUI).  
- Keep .env private (it contains secrets).

Run the database migrations and create the storage symlink (so uploaded images are served):

```bash
php artisan migrate
php artisan storage:link
```

Start the servers (two terminals are easiest):

```bash
# Terminal A: PHP server
php artisan serve

# Terminal B: Vite dev (rebuilds CSS/JS on save)
npm run dev
```

Open http://127.0.0.1:8000 in your browser. You should see the app.

### Description:
A web app to manage math questions and answers for the “Copa Cangur” style activities.

- Teachers can create questions, group them in folders (called “Blocks”), and reorder them with drag and drop.
- Students can view questions and submit their answers as text, a video link, or an image.
- There is a clean “View mode” (student-like) and an “Edit mode” (for teachers). You can switch between them.

Note: This project is built with Laravel 12 (PHP 8.2+) and uses Blade views, Eloquent models, and simple routes. If you’re new to Laravel, don’t worry — follow the steps below and you’ll be fine.


#### What does each file/directory do:
###### Inside app:
- Traits: My models use built-in Laravel traits such as HasFactory (for factory support in testing/seeding) and Notifiable (added by Breeze for notifications). I did not define any custom traits in this project.

- View: In Laravel you can use layout and component files with `@extends('layouts.app')` and `@section('content') @endsection`, with `<x-layout>(Your content)</x-layout>` (like I do) or you can create a class in
  app/View/Components directory so that you use the `<x-app-layout>` tag even when your layout file isn't on the expected route (with the previous option you would use `<x-layouts.app>` for example) allowing Laravel to
  see the tag and recognize it has to look for a class that tells it what view to load. I didn't use this way in my project, but it has the benefit of being able to inject data through that class.

- In Providers we have AppServiceProvider which can be used to define authorization gates: simple permission checks that return a boolean (like the one that checks if the user is a teacher);
  and to create custom validation rules (like 'allowed_emails' that checks whether an email has an allowed domain; that's where /config/allowed_emails.php's function comes from,
  it's just to organize allowed emails). Whatever is in the boot function is always loaded so that it can be used.

- Policies: as the name suggests, these are files where you declare authorization methods that you can easily call with `authorize('(the method)', (any arguments you pass))` which will either continue loading the code
  or throw a permission denied exception. You can also use `can('(the method)', (any arguments you pass))` which will return a boolean according to the method implementation. Policies are where authorization rules are usually
  declared in a Laravel project and their methods are usually used in routes, controllers, or views via authorization helpers. In my project I use policies to determine who can create, edit, delete and see answers and questions
  which is essential for the type of web I am developing.

- Models: these files are used to define the relationships between objects and the way we create new instances of them. For example, in my code I have the Answer object with its own model where I define that an answer belongs to
  a question and that it also belongs to a user, its author. In there I also have a `protected $fillable` which defines which fields I can mass assign (that is, assign more than one field at a time) and a `protected $casts` which
  specifies what the type of each field should be to avoid errors like using a string as an integer.

- Http: Here we have Controllers, which are in charge of all the logic behind each endpoint so, as the name suggests, they control the functionality of the app, which view to load, the changes in the database...
  The Controller.php file is the base class for all the other controllers so you can also use it to add things to all controllers. I didn't. The controllers in the Auth directory and the ProfileController came with Breeze, a Laravel
  starter pack that is used to avoid reinventing the wheel each time we need authentication (which is almost always), so I didn't write them, but they use the same structure of creating a CRUD with forms for users, validate the users data,
  log them in giving them a session cookie (like we did in Flask) and updating the database if needed. On top of that, it adds some other fancy functions that it assumes you will need such as mail verification (which I didn't implement here
  because it doesn't make sense in development) or password reset in case you forgot it (same as verified email). Breeze also uses a type of controllers I didn't use which are the invocables, like VerifyEmailController.php, which only have a method
  so it is automatically called when a route points to the controller class. My controllers implement a usual CRUD and in QuestionController I have an additional method for the endpoint of the typeahead react component, managing the query and returning it as a json to the component.
  In this directory we also have Requests which are used to validate authorization and input data before the controller is called. Their methods aren't called, instead, you use `public function store(LoginRequest $request)` for example
  and Laravel automatically runs the requests' methods. In my project I have ProfileUpdateRequest which validates a name and an email and LoginRequest which handles login authentication, validates the credentials and prevents brute-force attacks by limiting
  the amount of authentication attempts. These two files came with Breeze and I didn't implement other ones because I didn't have the need, but they taught me how Laravel follows the SRP (Single Responsibility Principle) and a structured way to handle validation and authorization before controllers run.


###### Inside bootstrap:
- app.php: this file is responsible for Laravel to initialize and telling it where to look for routes and custom commands, which middleware to use and handling exceptions. It's the first file to load and as well as all the directory, is automatically generated
  when you create a new Laravel project.

- providers.php: tells Laravel which providers to load in the boot.

- cache: these are compiled routes, config and services for performance.


###### Inside config:
These are all configuration files that load at boot and use values defined in the .env.
- app.php: responsible for app name, language and time settings and debugging.

- auth.php: responsible for login and passwords resets.

- database.php: responsible for database connections configuration.

- cache.php: caching settings, mostly performance.

- session.php: session storage config.

- filesystems.php: describes files mapping in storage.

- logging.php: describes logs configuration, used in debugging.

- mail.php: describes email settings, I will use it when I configure email verification.

- queue.php: describes queued jobs, which I didn't use in my project.

- services.php: third-party services configuration.

- allowed_emails: custom configuration file used as a whitelist for the email domains allowed


###### Inside database:
- database.sqlite: The project was initially scaffolded with SQLite (Laravel default), which is why database/database.sqlite exists. During development I configured the application to use a MySQL database instead, 
  which is the database actually used by the project. The SQLite file is unused and kept only as part of the default Laravel structure.

- factories: classes to seed the database with fake data. Very useful in development.

- migrations: the log of all the edits on the database so that every person with my code could run `php artisan migrate` and have the same tables as me.

- seeders: as the name suggests, is a class where you can define which factories to call when you want to seed the database so that you can do it with only one command: `php Artisan db:seed`


###### Inside lang:
- ca and ca.json: files with status messages and their translations


###### Inside public:
All these files are the only ones exposed to the browser.
- index.php: this file belongs to Laravel scaffolding, so I didn't create it. It loads Composer’s autoloader, boots Laravel and hands the request to Laravel.

- favicon.ico: an image I uploaded to use as the logo for my web (it means above average).

- hot: only for development and tells Laravel that Vite dev server is running: `npm run dev`

- robots.txt: instructions for search engines, from Laravel also

- build: compiled front assets generated by `npm run build`

- storage: is a symbolic link that allows to serve user-uploaded files without revealing all the storage directory, created by: `php artisan storage:link`

- .htaccess: configures Apache to route all requests through public/index.php, enables Laravel routing, CSRF protection, and clean URLs.


###### Inside resources:
 Inside css:
   - app.css: is the project's css file with some fixes to Tailwind class conflicts.

 Inside js:
   - app.js: is the frontend entry point. It initializes Laravel’s JavaScript environment, starts Alpine.js for lightweight UI interactions, and conditionally mounts a React Typeahead component into specific DOM elements using dynamic imports.   
     React is used only where needed and receives its configuration via HTML data-* attributes, allowing Blade templates and React components to coexist cleanly. App.js is executed every time a page loads, initializing Alpine.js and mounting
     React components by scanning the DOM for specific placeholder elements.

   - bootstrap.js: loads Axios, a JavaScript HTTP client used to send GET, POST, PUT, and DELETE requests from the browser to the Laravel backend. 
     It also configures default headers (including X-Requested-With) and integrates with Laravel’s CSRF protection so AJAX requests are accepted securely.
     The frontend uses AJAX requests (via Axios) to communicate with Laravel endpoints asynchronously, allowing dynamic UI updates without full page reloads.

  - Typeahead.jsx (inside components): implements a React autocomplete input that queries a Laravel endpoint via Axios as the user types, displays matching results, 
    and writes the selected option’s id into a hidden form field so standard form submissions work. This file and the app.js, were essentially implemented using AI assistants because I wanted to avoid having to deeply learn additional frontend 
    frameworks and tooling on top of the already new to me Laravel, php, Eloquent and Blade syntax. However, I did go over all the files making sure I understand the logic behind them.

 Inside views:
  - In answers directory we found the CRUD corresponding views which use html, tailwind class and blade directives to use components, layouts and simple php logic without directly writing php. In the create and edit views
    I also used some JavaScript to change the view depending on the type of solution without changing pages.

  - In auth directory we found the views that Breeze implemented for the authentication which are very similar to my CRUD ones and also use other components it also created.

  - In components directory is where all the components and layouts that other views reference usually live. To use a component you simply use the tag `<x-(the name of the file)>` or `@include('(component route from views)')` and if you want to 
    pass variables you can pass them as props with `<x-component :prop="value">`. If you want to surround some code with a component or layout, you write `{{ $slot }}` in the component or layout, which Blade compiles to PHP’s escaped echo `<?php echo e($slot); ?>`. 
    In my project I use a lot of components because they are a very nice way to keep your project clean and avoid hundreds of lines in one single file.

  - In profile we have more Breeze's views related to user profile. Those `x-...` attributes in the HTML elements are Alpine.js directives. These are JavaScript instructions embedded in HTML that enable lightweight client-side interactivity 
    (such as toggles and dropdowns) without requiring a full JavaScript framework. They are also used in other views. For example: `<x-button x-on:click="open = !open" />` in Alpine.js would be the equivalent in JavaScript of 
    `document.querySelector('#btn').addEventListener(...)`
 
  - In questions we have the corresponding questions CRUD views which are implemented in the same way as all the others. It's worth noting that x-cloak directive at the end of the file and as an attribute in some elements which is from Alpine.js
    and hides those elements while Alpine.js has not finished initializing yet to avoid visual bugs.

  - /resources/views/vendor contains customized pagination view templates that override Laravel’s default pagination views.

  - home.blade.php: is a simple view that tells the user when it has succesfully logged in, which I may use for other features in the future. 
    Notice that .blade extensions in all view files that allows Blade to work.


###### Inside routes:
- console.php is a default routes file that Laravel recognizes and loads (as well as api.php, channels.php and web.php), in my project it doesn't have any functionality, 
  but I kept it because it comes with a funny demo and because it allows to create custom Artisan CLI commands such as bulk-loading questions which I might implement in the future.

- auth.php is the file that Breeze creates to manage its routes. Since its not a Laravel default it has to be referenced in web.php with `require __DIR__.'/auth.php';`. Here you can see how every route is essentially defined with a method and
  the method it loads form a controller. We can also see how some routes have a name so that if we change an endpoint and we have a form that submited to that route via `route('(the route name)')` it's still pointing to the correct route. 
  Other common directive is `->middleware('(your rules)')` which applies middleware such as authentication or authorization checks before the controller is reached. 

- web.php is the default Laravel file for routes which uses the same structure as in auth.php and also some route grouping functions that Laravel conventions make possible. For example `Route::resource` automatically lists all routes according to the 
  controller's methods or `Route::middleware('auth')->group(function () {` applies authentication middleware to all the routes inside of it. While designing routes, I used a simple Artisan command which displays all your routes and helps you make sure you are getting what you wanted (`php artisan route:list`).
  All that class imports in the files with `use ...` allow us to reference that class without writing the whole path to it each time.

###### Inside storage:
   It contains runtime data such as logs, cache files, and user-uploaded content. It is not directly accessible from the browser. A symbolic link (public/storage) exposes only storage/app/public so uploaded files can be served securely 
   without revealing internal application data, as mentioned earlier in public directory.

###### Inside tests:
   It contains automated tests provided by Laravel and Laravel Breeze. These are mainly feature tests that verify authentication, profile management, and basic application behavior. I did not write custom tests for this project, 
   but the existing tests helped me understand Laravel’s testing structure. Tests can be run with `php artisan test` and are very useful for being aware of which functionalities you may break while you are developing your code.
   The TestCase file is a base class that all tests extend.



- .env and .env.example: they are environment files and examples where the user declares all the information Laravel may look for. The .env.example exists so that you can copy it and create your .env because the .env is never shared.
- composer.json: declares which PHP packages the project depends on for the composer. Generated automatically.
- composer.lock: locks the versions installed so they are known. Generated automatically.
- package.json: declares frontend tools for npm (node package manager). Generated automatically.
- package-lock.json: locks the versions installed. Generated automatically. 
- vite.config.js: configures Vite, the frontend build tool and declares the entry points. Generated automatically.
- tailwind.config.js: configures Tailwind and allows theme extensions (colors, fonts, sizes...). Generated automatically and didn't use it yet, but I may if I create a specific color for the project or a specific font.
- postcss.config.js: configures PostCSS and runs Tailwind and Autoprefixer before CSS reaches the browser. Generated automatically.
- Artisan: Laravel's CLI tool. Generated automatically.

Standard framework and dependency directories (such as vendor/ and node_modules/) are not described in detail, as they are managed automatically by Composer and npm.
