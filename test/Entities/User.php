<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Noj\Fabrica\Test\Entities\Post;

#[ORM\Entity]
#[ORM\Table('users')]
class User
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	#[ORM\GeneratedValue]
	public $id;

	#[ORM\Column("first_name")]
	public $firstName = '';

	#[ORM\Column("last_name")]
	public $lastName = '';

	#[ORM\Column]
	public $age;

	#[ORM\Embedded(Address::class)]
	public $address;
	
	#[ORM\Column]
	public $banned = false;

	#[ORM\OneToMany("user", Post::class, ['persist', 'remove'])]
	public $posts;

	public function __construct()
	{
		$this->posts = new ArrayCollection();
	}

	public function getFirstName(): string
	{
		return $this->firstName;
	}

	public function setFirstName(string $firstName)
	{
		$this->firstName = $firstName;
	}

	public function getLastName(): string
	{
		return $this->lastName;
	}

	public function setLastName(string $lastName)
	{
		$this->lastName = $lastName;
	}

	public function addPost(Post $post)
	{
		$this->posts->add($post);
	}
}
