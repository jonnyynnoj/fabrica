# Fabrica

![Pipeline](https://img.shields.io/github/actions/workflow/status/jonnyynnoj/fabrica/tests.yml?style=flat-square)
[![Latest Stable Version](https://img.shields.io/packagist/v/noj/fabrica?style=flat-square)](https://packagist.org/packages/noj/fabrica)
![PHP Version Support](https://img.shields.io/packagist/php-v/noj/fabrica?style=flat-square)
![GitHub](https://img.shields.io/github/license/jonnyynnoj/fabrica?style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/noj/fabrica?style=flat-square)](https://packagist.org/packages/noj/fabrica)

Fabrica handles the definition and creation of objects for use in testing.

Coupled with its Doctrine integration, Fabrica allows you to fully test CRUD logic without mocking. See the [What Problem Does it Solve?](https://github.com/jonnyynnoj/fabrica/wiki/What-Problem-Does-it-Solve%3F) page for a full explanation.

___

- [Installing](#installing)
- [Usage](#usage)
    - [Setup](#setup)
    - [Basic Usage](#basic-usage)
    - [Setters](#setters)
    - [Overrides](#overrides)
    - [Create Multiple](#create-multiple)
    - [Relations](#relations)
        - [Overriding Relation Properties](#overriding-relation-properties)
    - [Entity Types](#entity-types)
        - [Extending](#extending)
    - [Doctrine Integration](#doctrine-integration)
        - [Refreshing the Database Between Tests](#refreshing-the-database-between-tests)
        - [PHPUnit Assertions](#phpunit-assertions)
    - [Faker Integration](#faker-integration)
- [Inspirations](#inspirations)

## Installing

```
composer require noj/fabrica --dev
```

## Usage

### Setup

First initialise Fabrica somewhere within your test suite. For PHPUnit, this can be done using the [bootstrap](https://phpunit.readthedocs.io/en/9.1/organizing-tests.html?highlight=bootstrap#composing-a-test-suite-using-xml-configuration) option:
```php
<?php
require 'vendor/autoload.php';
Fabrica::loadFactories([__DIR__ . '/factories']);
```

### Basic Usage

Assuming that you have a `User` entity, create a new factory `factories/UserFactory.php`:
```php
Fabrica::define(User::class, fn() => [
    'username' => 'user123',
    'firstName' => 'Test',
    'lastName' => 'User',
]);
```

Now within your test, you can create a new `User` instance:
```php
$user = Fabrica::create(User::class);

// Check properties have been set
assertEquals('user123', $user->username);
assertEquals('Test', $user->firstName);
assertEquals('User', $user->lastName);
```

### Setters

If your entity's properties are only accessible through setters then you can use the `@` syntax to call methods instead:
```php
Fabrica::define(User::class, fn() => [
    '@setUsername' => 'user123',
    '@setFirstName' => 'Test',
    '@setLastName' => 'User',
]);
```

Given that a value is an array, you can indicate that you wish the method to be called for each item using the multiple times suffix (`*`):

```php
'@addPermission*' => ['USER', 'ADMIN'],
```

### Overrides

You can override any default values when creating your entity:

```php
/// UserFactory.php
Fabrica::define(User::class, fn() => [
    'username' => 'user123',
    'firstName' => 'Test',
    'lastName' => 'User',
    '@setAge' => 47,
]);

/// UserTest.php
$user = Fabrica::create(User::class, fn() => [
    'firstName' => 'Another',
    '@setAge' => 24,
]);

assertEquals('user1223', $user->username);
assertEquals('Another', $user->firstName);
assertEquals('User', $user->lastName);
assertEquals(24, $user->getAge());
```

### Create Multiple

You can create multiple entities like so:

```php
$users = Fabrica::createMany(User::class, 3);
```

### Relations

You can automatically create related entities. For example, if you have `Comment` entity that belongs to a `User` then you can define a factory:

```php
Fabrica::define(Comment::class, fn() => [
    'title' => 'A test comment',
    'body' => 'This is a test',
    'author' => Fabrica::create(User::class),
]);
```

Whenever a `Comment` is created it will have an associated `User`:

```php
$comment = Fabrica::create(Comment::class);

assertInstanceOf(User::class, $comment->user);
assertEquals('user123', $comment->user->username);
```

You can also define the inverse side of the relation. For example, you can define that each created `User` should have an associated `Comment`:

```php
Fabrica::define(User::class, fn() => [
    '@setUsername' => 'user123',
    '@setFirstName' => 'Test',
    '@setLastName' => 'User',
    '@addComment' => Fabrica::create(Comment::class)
]);
```

You can create multiple child relations:

```php
Fabrica::define(User::class, fn() => [
    'comments' => Fabrica::createMany(Comment::class, 3),

    // or if you have a setter method, use the `*` suffix to call the method
    // once for each element of the array
    '@addComment*' => Fabrica::createMany(Comment::class, 3),
]);
```
Will create a `User` with 3 `Comments`.

If the entity has a property that depends on the relation then you can define this like so:

```php
Fabrica::define(Comment::class, fn() => [
    'user' => Fabrica::create(User::class),
    'userFirstName' => Fabrica::property('user.firstName'),
]);
```

#### Overriding Relation Properties

You can also override properties of nested relations when creating an entity:

```php
$comment = Fabrica::create(Comment::class, fn() => [
    'author.firstName' => 'John'
]);

assertEquals('user123', $comment->user->username);
assertEquals('John', $comment->user->firstName);
```

For a single entity of a one-to-many relation:

```php
$user = Fabrica::create(User::class, fn() => [
    'comments.1.title' => 'Only the 2nd comment has this title'
]);

assertEquals('A test comment', $user->comments[0]->title);
assertEquals('Only the 2nd comment has this title', $user->comments[1]->title);
```

Or even every entity:

```php
$user = Fabrica::create(User::class, fn() => [
    'comments.*.title' => 'Each comment now has this title'
]);

foreach ($user->comments as $comment) {
    assertEquals('Each comment now has this title', $comment->title);
}
```

### Entity Types

Rather than always having to pass overrides when creating entities, you can define different types of entities:

```php
Fabrica::define(User::class, fn() => [
    'username' => 'bannedUser',
    'firstName' => 'Test',
    'lastName' => 'User',
])->type('banned');

$normalUser = Fabrica::create(User::class);
$bannedUser = Fabrica::create(User::class, 'banned');
$bannedUser2 = Fabrica::create(
    User::class,
    'banned',
    fn() => ['firstName' => 'banned'],
);
```

#### Extending

If a sub-type shares attributes with the parent-type then you can specify that the factory extends from it:

```php
Fabrica::define(User::class, fn() => [
    'username' => 'bannedUser'
])->type('banned')->extends(User::class);

$bannedUser = Fabrica::create(User::class, 'banned');
assertEquals('bannedUser', $bannedUser->username);
assertEquals('Test', $bannedUser->firstName);
assertEquals('User', $bannedUser->lastName);
```

You can also extend from a sub-type:

```php
Fabrica::define(User::class, fn() => [
    'permanent' => true
])->type('permaBanned')->extends(User::class, 'banned');
```

### Doctrine Integration

Fabrica ships with a Doctrine adapter that will automatically persist your entities on creation. Simply configure the store in your bootstrap file:

```php
Fabrica::setStore(new DoctrineStore($entityManager));
```

Where the EntityManager comes from and how it is configured may depend on your application.

Fabrica provides a simple way of creating an annotation backed EntityManager using in-memory SQLite if that meets your requirements:
```php
$entityManager = \Noj\Fabrica\Adapter\Doctrine\EntityManagerFactory::createSQLiteInMemory([__DIR__ . '/path/to/entities']);
Fabrica::setStore(new DoctrineStore($entityManager));
```

#### Refreshing the Database Between Tests

Most likely you will want to reset the state of your database before each test runs. There are 2 ways of doing this:

- If you are using PHPUnit 7.5 or above then you can add the following to your `phpunit.xml` which will reset your database between each test:
    ```xml
    <extensions>
        <extension class="Noj\Fabrica\Adapter\Doctrine\PHPUnit\RefreshDatabase" />
    </extensions>
    ```
- If you are using a lower version of PHPUnit or you would only like to create the database for specific tests then you can add the trait to your test class:
    ```php
    class MyTest extends TestCase
    {
        use \Noj\Fabrica\Adapter\Doctrine\PHPUnit\NeedsDatabase;
    }
    ```
  
#### PHPUnit Assertions

Fabrica ships with a set of PHPUnit assertions for validating the state of the database during a test.
```php
class MyTest extends TestCase
{
    use \Noj\Fabrica\Adapter\Doctrine\PHPUnit\DatabaseAssertions;
}
```

This provides the following assertions:

- `assertDatabaseContainsEntity(string $class, array $criteria = [])`
- `assertDatabaseContainsEntities(string $class, int $amount, array $criteria = [])`
- `assertDatabaseContainsExactlyOneEntity(string $class, array $criteria = [])`
- `assertDatabaseDoesNotContainEntity(string $class, array $criteria = [])`

Note: If you are using `NeedsDatabase` described above then the assertions are already included.

Example usage:
```php
public function test_it_creates_a_user()
{
    (new UserCreator)->create('test');
    self::assertDatabaseContainsEntity(User::class, ['username' => 'test'])'
}
```

### Faker Integration

Fabrica can be configured with a [faker](https://github.com/fzaninotto/faker) instance to help generate fake data in your entity definitions.

```php
$faker = \Faker\Factory::create();
Fabrica::addDefineArgument($faker);
``` 

You will then receive the faker instance in the define callback:

```php
use Faker\Generator as Faker;

Fabrica::define(User::class, fn (Faker $faker) => [
    'firstName' => $faker->firstName,
    'lastName' => $faker->lastName,
    'email' => $faker->email,
]);
```

## Inspirations

- [Laravel Factories](https://laravel.com/docs/database-testing)
- [Factory Muffin](https://github.com/thephpleague/factory-muffin)
