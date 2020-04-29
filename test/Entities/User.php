<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Entities;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="users")
 */
class User
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	public $id;

	/** @Column(name="first_name") */
	public $firstName = '';

	/** @Column(name="last_name") */
	public $lastName = '';

	/** @Column */
	public $age;

	/** @OneToMany(targetEntity="Noj\Fabrica\Test\Entities\Post", mappedBy="user", cascade={"persist", "remove"}) */
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
