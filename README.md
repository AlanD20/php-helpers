# PHP Custom Helper Classes

A few PHP helper classes I have created. You could copy the file content and use
it in your projects.

<details>
<summary><b>Database Documentation</b></summary>

## Database Documentation

A simple class that makes executing query easier with full PDO and bindings.

- Easy to setup
- Easy way to dump query.
- Supports raw SQL queries.
- Supports both associative and indexed bindings.

### Setup

Simply copy the entire file and put it somewhere. For this example we will put
it in `app/Service/Database.php`

### Create New Instance

```php
require __DIR__ . '/app/Service/Database.php';

// Create an instance and provide database credentials
$db = new Database('mydatabase', 'root', '');
```

### Raw Query

You may perform raw queries, here are a few examples

```php
// raw query
$db->rawQuery('
    CREATE TABLE users (
      id INT PRIMARY KEY AUTO_INCREMENT,
      name VARCHAR(255) NULL,
      email VARCHAR(255) NULL,
      phone VARCHAR(255) NULL
    )');

// Perform any raw queries
$db->rawQuery('
    INSERT INTO users(name, email, phone) VALUES ("aland", "aland20@pm.me", "123");
');

// Raw results from PDO object
$rawResult = $db->rawQuery('SELECT * FROM users');
```

### Built-in Methods

You may use built-in methods to easily work with SQL queries.

### Select Table/Columns

Before each query, you may select a table and its columns to perform queries on.

```php
// You could use literal strings for columns
$db
    ->table('users')
    ->columns('name, email');

// You may use array of strings that each element represent a column name
$db
    ->table('users')
    ->columns(['name', 'email']);
```

### Select

**Args**:

- condition (string|array) optional.
- bindings (array) optional.

```php
// Fetch All
$db
    ->table('users')
    ->columns(['name', 'email'])
    ->select()
    ->fetchAll();

// All Columns
$db
    ->table('users')
    ->columns('*')
    ->select()
    ->fetchAll();

// Fetch One
$db
    ->table('users')
    ->columns('*')
    ->select()
    ->fetch();

// Conditions
$db
    ->table('users')
    ->columns('*')
    ->select('id = 3')
    ->fetch();

// Conditions with bindings, supports associative & indexed arrays
$db
    ->table('users')
    ->columns('*')
    ->select('id = ? and name = ?', [3, 'aland'])
    ->fetch();

$db
    ->table('users')
    ->columns('*')
    ->select('id = :id and name = :name', ['id' => 3, 'name' => 'aland'])
    ->fetch();
```

### Insert

**Args**:

- values (string|array) required.
- bindings (array) optional.

```php
// No bindings, each value has to be in single quotes.
$db
    ->table('users')
    ->columns(['name', 'email'])
    ->insert("'aland', 'aland20@example.com'");

// bindings, supports associative & indexed arrays. Returns primary key.
$db
    ->table('users')
    ->columns(['name', 'email'])
    ->insert('?, ?', ['aland','aland20@example.com']);

$db
    ->table('users')
    ->columns(['name', 'email'])
    ->insert(':name, :email', [
        'name' => 'aland',
        'email' => 'aland20@example.com',
    ]);
```

### Insert Many

**Args**:

- list (array) required.

```php
// two-dimensional array, only supports indexed values with corresponding columns
$db
    ->table('users')
    ->columns(['name', 'email'])
    ->insertMany([
      ['johndoe', 'johndoe@example.com'],
      ['jackfrank', 'jackfrank@example.com'],
    ]);
```

### Update

**Args**:

- condition (string) optional.
- bindings (array) optional.

```php
// No bindings, each value has to be in single quotes.
$db
    ->table('users')
    ->columns([
      'name' => 'aland',
      'email' => 'aland20@example.com',
    ])
    ->update('id = 2');

// 2 dimensional array, only supports indexed values with corresponding columns
// Notice all placeholders for where condition must be sequentially start after
// the placeholder columns
$db
    ->table('users')
    ->columns("name = ?, email = ?")
    ->update('id = ?', ['aland', 'aland20@example.com', 2]);

$db
    ->table('users')
    ->columns("name = :name, email = :email")
    ->update('id = :id', [
       'name' => 'aland',
       'email' => 'aland20@example.com',
       'id' => 2
     ]);

// You could do the following as well, but don't forget the '?'
$db
    ->table('users')
    ->columns([
       'name' => '?',
       'email' => '?',
    ])
    ->update('id = ?', ['aland', 'aland20@example.com', 2]);
```

### Delete

Defining columns are not required.

**Args**:

- condition (string) optional.
- bindings (array) optional.

```php
// No bindings, each value has to be in single quotes.
$db->table('users')->delete('id = 2');

// 2 dimensional array, only supports indexed values with corresponding columns
$db->table('users')->delete('id = ?', [2]);

$db->table('users')->delete('id = :id', ['id' => 2]);
```

### Dumping Queries & Debugging

This class exposes two methods that allows you to easily debug and see the query
you have performed. Also, using a die dump method to dump anything you would
like to see.

To dump a query, the query has to be executed, after that, you may run `toSql()`
on the database instance.

- Default is false. If first argument is true, it shows parameters if it had any
  to be executed.

```php
$db
    ->table('users')
    ->columns('*')
    ->select()
    ->fetchAll();

$db->toSql(); // Prints the following details

// Query Statement:
// SELECT * FROM users


$db->toSql(true); // Prints the following details

// Query Statement:
// SELECT * FROM users

// Binding Parameters To Statement:
// Array
// (
// )
```

</details>

<details>
<summary><b>UploadFile Documentation</b></summary>

## UploadFile Documentation

This class uploads temporary file to given path. This is a very simple implementation where it uses the temporary file mime type to detect the file extension. This may not be very secure.

### Setup

```html
<form method="post" enctype="multipart/form-data">
  <input name="images[]" type="file" multiple />
</form>
```

```php
// Notice we use images, because it has to be arrays of images and it must be multiple files, otherwise, it doesnt work.
$files = $_FILES['images'];
$publicPath = __DIR__ . '/../public';

$uploader = new UploadFile($files, $publicPath);

// Return an array of paths to all the uploaded images.
$uploaded = $uploader->save();
```

</details>

---

## License

This repository is under [MIT](./LICENSE) license.
