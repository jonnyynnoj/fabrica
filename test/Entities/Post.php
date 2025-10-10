<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Entities;

use Doctrine\ORM\Mapping as ORM;
use Noj\Fabrica\Test\Entities\User;

#[ORM\Entity]
#[ORM\Table('posts')]
class Post
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	#[ORM\GeneratedValue]
	public $id;

	#[ORM\ManyToOne(User::class, ["persist", "remove"], inversedBy: "posts")]
	public $user;

	#[ORM\Column]
	public $title;

	#[ORM\Column]
	public $body;

	public $userFirstName;
}
