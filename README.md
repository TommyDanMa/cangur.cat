# Web Copa Canguro (Laravel)

A simple web app to manage math questions and answers for the “Copa Canguro” style activities.

- Teachers can create questions, group them in folders (called “Blocks”), and reorder them with drag and drop.
- Students can view questions and submit their answers as text, a video link, or an image.
- There is a clean “View mode” (student-like) and an “Edit mode” (for teachers). You can switch between them.

Note: This project is built with Laravel 12 (PHP 8.2+) and uses Blade views, Eloquent models, and simple routes. If you’re new to Laravel, don’t worry — follow the steps below and you’ll be fine.


## 1) Quick Start (what we’ll do)

1) Install dependencies (Composer for PHP, npm for assets).  
2) Configure your .env (database settings).  
3) Run the database migrations (this creates the tables).  
4) Start the dev servers (PHP + Vite) and open the site.  
5) Create your first user and make it a teacher (so you see the edit tools).

(Why these steps? Laravel apps need PHP packages and a database. The migrations build the database tables. Vite builds the CSS/JS. A teacher role unlocks the “manage” features.)


## 2) Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+ and npm
- A database (MySQL or MariaDB is typical; SQLite can also work for quick tests)

Tip: On macOS/Linux you can install PHP and Composer with your package manager. On Windows, try Laragon or WAMP. If you already develop Laravel projects, you likely have all of this.


## 3) Installation

Clone and install dependencies:

```bash
# 1) Clone
git clone <your-fork-or-this-repo-url> web-copa-canguro
cd web-copa-canguro

# 2) PHP deps
composer install

# 3) Node deps (for styles/JS during development)
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
DB_DATABASE=web_canguro
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

- Use a real database and user here. If the database doesn’t exist yet, create it (you can do this in your MySQL client or GUI).  
- Prefer 127.0.0.1 over localhost to avoid socket vs TCP issues on some systems.  
- Keep .env private (it contains secrets).

Run the database migrations and create the storage symlink (so uploaded images are served):

```bash
php artisan migrate
php artisan storage:link
```

Start the servers (two terminals is easiest):

```bash
# Terminal A: PHP server
php artisan serve

# Terminal B: Vite dev (rebuilds CSS/JS on save)
npm run dev
```

Open http://127.0.0.1:8000 in your browser. You should see the app.


## 4) Create your first user (and promote to teacher)

1) Click Register and create an account.  
2) Verify your email if asked (Laravel’s email verification is enabled).  
3) Make the user a teacher (teachers can create/edit questions and blocks).

There are two easy ways to promote a user to teacher:

- Using Tinker (recommended for beginners):

```bash
php artisan tinker
```
Then in the Tinker prompt:
```php
$user = App\Models\User::first();
$user->role = 'teacher';
$user->save();
```

- Or, run a quick SQL query in your DB tool:
```sql
UPDATE users SET role = 'teacher' WHERE email = 'your@email.com';
```

Note: There is also a safety migration that tries to promote the first user to teacher if no teacher exists yet, but it only runs during migrations. If you created your user after running `php artisan migrate`, use one of the methods above.


## 5) What you can do (usage guide)

- Browse questions
  - The home page redirects to the Questions list. Students can read questions here.

- Add a new question (teacher only)
  1) Log in as a teacher.  
  2) Go to “New question” (URL: /questions/create).  
  3) Fill in: title, statement, year (number), level (text like A/B/C), phase (local/semifinal/final), and optionally session (mati or tarda) and an image.  
  4) Optionally assign the question to a Block (folder). If you don’t, it stays in the “Unassigned” area.  
  5) Save — the question appears in the list.
  
  Tip: Position is handled automatically. If you assign a block, the question is placed at the end of that block.

- Submit an answer (student)
  1) Log in.  
  2) Open a question.  
  3) Click “Answer” and choose a format: text, video URL, or image upload.  
  4) Optionally, provide the numeric solution (or “0000” to say there is no solution).  
  5) Submit — you’ll be redirected to your answer page.

- Organize with Blocks (teacher only)
  - Blocks are like folders. You can create blocks, nest them (parent/child), and drag-and-drop to reorder.  
  - On the Questions page, teachers see a Blocks panel on the left (or top, depending on your screen).  
  - Go to a Block to see the questions inside it. Drag to reorder, then click “Save order”.  
  - Use the “Move to…” menu to move a question into another block.

- Switch between Edit and View mode (teacher)
  - A small toggle [Veure | Edita] appears on the Questions and Block pages.  
  - Edit mode shows handles, borders, and extra buttons. View mode hides them (useful to preview the student view).

Helpful API endpoints (for advanced users; you don’t need these to use the UI):
- POST /blocks/reorder — JSON body: [{ id, parent_id, position }]
- POST /questions/reorder — JSON body: [{ id, block_id, position }]


## 6) Project structure (where things live)

- routes/web.php — App routes (URLs). This is where you can see what pages/actions exist.  
- app/Models
  - User.php — User model (has role: student or teacher).  
  - Question.php — Question model (title, statement, year, level, phase, session, image_path, block_id, position).  
  - Answer.php — Answer model (format text/video/image; stores content and optional solution).  
  - Block.php — Folder-like model to group questions (supports nesting and ordering).
- app/Http/Controllers
  - QuestionController.php — Lists, creates, updates, deletes questions; also reorders and moves them between blocks.  
  - AnswerController.php — Create, edit, view, and delete answers.  
  - BlockController.php — Create, edit, delete blocks; reorder and show contents.  
- resources/views — Blade templates (HTML). Pages for questions, answers, blocks, and layouts.  
- database/migrations — Files that create and modify the DB tables (run by `php artisan migrate`).  
- public/storage — Symlink to storage for uploaded images (created by `php artisan storage:link`).

(Reading tip: If you’re curious how a page works, check its route in routes/web.php, then open the matching Controller method, then the Blade view it returns.)


## 7) Common commands (copy/paste friendly)

```bash
# Install deps
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# DB
php artisan migrate
php artisan storage:link

# Run (two terminals)
php artisan serve
npm run dev
```

Optional: promote first user to teacher
```bash
php artisan tinker
>>> $u = App\Models\User::first(); $u->role = 'teacher'; $u->save();
```


## 8) Notes and troubleshooting

- Can I use SQLite? Yes, for quick testing. Update .env: `DB_CONNECTION=sqlite` and set `DB_DATABASE=/absolute/path/to/database.sqlite` (then run `touch database/database.sqlite`).  
- I can’t see teacher buttons? Make sure your user’s `role` is `teacher` (see the Tinker/SQL steps above).  
- Images not showing? Run `php artisan storage:link` and reload the page.  
- Email verification: If emails don’t send in local, set `MAIL_MAILER=log` in .env to log them instead (you can copy the verification link from the logs).


## 9) License

MIT for this project code (Laravel itself is MIT too).
