# Fabrica

Fabrica handles the definition and creation of objects for use in testing.

## Installing

```
composer require noj/fabrica --dev
```

## Usage

### Setup

First initialise Fabrica somewhere within your test suite. For PHPUnit, this can be done using the `bootstrap` option:
```php
Fabrica::init();
Fabrica::loadFactories([__DIR__ . '/factories']);
```

### Basic Usage

Assuming that you have a `User` entity, create a new factory `factories/UserFactory.php`:
```php
Fabrica::define(User::class, function () {
    return [
        'username' => 'user123',
        'firstName' => 'Test',
        'lastName' => 'User',
    ];
});
```

Now within your test, you can create a new `User` instance:
```php
$user = Fabrica::create(User::class);

// Check properties have been set
self::assertEquals('user123', $user->username);
self::assertEquals('Test', $user->firstName);
self::assertEquals('User', $user->lastName);
```

### Setters

If your entity's properties are only accessible through setters then you can use the `@` syntax to call methods instead:
```php
Fabrica::define(User::class, function () {
    return [
        '@setUsername' => 'user123',
        '@setFirstName' => 'Test',
        '@setLastName' => 'User',
    ];
});
```

Given that a value is an array, you can indicate that you wish the method to be called for each item using the multiple times suffix (`*`):

```php
'@addPermission*' => ['USER', 'ADMIN'],
```

### Overrides

You can override any default values when creating your entity:

```php
/// UserFactory.php
Fabrica::define(User::class, function () {
    return [
        'username' => 'user123',
        'firstName' => 'Test',
        'lastName' => 'User',
        '@setAge' => 47,
    ];
});

/// UserTest.php
$user = Fabrica::create(User::class, function () {
    return [
        'firstName' => 'Another',
        '@setAge' => 24,
    ];
});

self::assertEquals('user1223', $user->username);
self::assertEquals('Another', $user->firstName);
self::assertEquals('User', $user->lastName);
self::assertEquals(24, $user->getAge());
```

### Create Multiple

You can create multiple entities like so:

```php
$users = Fabrica::createMany(User::class, 3);
```

### Relations

You can automatically create related entities. For example, if you have `Comment` entity that belongs to a `User` then you can define a factory:

```php
Fabrica::define(Comment::class, function () {
    return [
        'title' => 'A test comment',
        'body' => 'This is a test',
        'author' => Fabrica::create(User::class),
    ];
});
```

Whenever a `Comment` is created it will have an associated `User`:

```php
$comment = Fabrica::create(Comment::class);

self::assertInstanceOf(User::class, $comment->user);
self::assertEquals('user123', $comment->user->username);
```

You can also define the inverse side of the relation. For example, you can define that each created `User` should have an associated `Comment`:

```php
Fabrica::define(User::class, function () {
    return [
        '@setUsername' => 'user123',
        '@setFirstName' => 'Test',
        '@setLastName' => 'User',
        '@addComment' => Fabrica::create(Comment::class)
    ];
});
```

You can create multiple child relations:

```php
Fabrica::define(User::class, function () {
    return [
        'comments' => Fabrica::createMany(Comment::class, 3),

        // or if you have a setter method, use the `*` suffix to call the method
        // once for each element of the array
        '@addComment*' => Fabrica::createMany(Comment::class, 3),
    ];
});
```
Will create a `User` with 3 `Comments`.

If the entity has a property that depends on the relation then you can define this like so:

```php
Fabrica::define(Comment::class, function () {
    return [
        'user' => Fabrica::create(User::class),
        'userFirstName' => Fabrica::property('user.firstName'),
    ];
});
```

#### Overriding Relation Properties

You can also override properties of nested relations when creating an entity:

```php
$comment = Fabrica::create(Comment::class, function () {
    return [
        'author.firstName' => 'John'
    ];
});

self::assertEquals('user123', $comment->user->username);
self::assertEquals('John', $comment->user->firstName);
```

For a single entity of a one-to-many relation:

```php
$user = Fabrica::create(User::class, function () {
    return [
        'comments.1.title' => 'Only the 2nd comment has this title'
    ];
});

self::assertEquals('A test comment', $user->comments[0]->title);
self::assertEquals('Only the 2nd comment has this title', $user->comments[1]->title);
```

Or even every entity:

```php
$user = Fabrica::create(User::class, function () {
    return [
        'comments.*.title' => 'Each comment now has this title'
    ];
});

foreach ($user->comments as $comment) {
    self::assertEquals('Each comment now has this title', $comment->title);
}
```

### Entity Types

Rather than always having to pass overrides when creating entities, you can define different types of entities:

```php
Fabrica::define(User::class, function () {
    return [
        'username' => 'bannedUser',
        'firstName' => 'Test',
        'lastName' => 'User',
    ];
})->type('banned');

$normalUser = Fabrica::create(User::class);
$bannedUser = Fabrica::create(User::class, 'banned');
$bannedUser2 = Fabrica::create(User::class, 'banned', function () {
    return ['firstName' => 'banned'];
});
```

#### Extending

If a sub-type shares attributes with the parent-type then you can specify that the factory extends from it:

```php
Fabrica::define(User::class, function () {
    return [
        'username' => 'bannedUser'
    ];
})->type('banned')->extends(User::class);

$bannedUser = Fabrica::create(User::class, 'banned');
self::assertEquals('bannedUser', $bannedUser->username);
self::assertEquals('Test', $bannedUser->firstName);
self::assertEquals('User', $bannedUser->lastName);
```

You can also extend from a sub-type:

```php
Fabrica::define(User::class, function () {
    return [
        'permanent' => true
    ];
})->type('permaBanned')->extends(User::class, 'banned);
```

### Doctrine Integration

Fabrica ships with a Doctrine implementation that will automatically persist your entities on creation.

```php
$store = new \Fabrica\Store\DoctrineStore($entityManager);
Fabrica::init($store);
```

You can see an example of configuring against an in-memory sqlite database in the [test directory](test/Store/DoctrineStoreTest.php).

### Faker Integration

Fabrica can be configured with a [faker](https://github.com/fzaninotto/faker) instance to help generate fake data in your entity definitions.

```php
$faker = \Faker\Factory::create();
Fabrica::addDefineArgument($faker);
``` 

You will then receive the faker instance in the define callback:

```php
use Faker\Generator as Faker;

Fabrica::define(User::class, function (Faker $faker) {
    return [
        'firstName' => $faker->firstName,
        'lastName' => $faker->lastName,
        'email' => $faker->email,
    ];
});
```
