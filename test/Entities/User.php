<?php declare(strict_types=1);

namespace Fabrica\Fabrica\Test\Entities;

/**
 * @Entity
 * @Table(name="users")
 */
class User
{
	/**
	 * @Id @Column(type="integer")
	 * @GeneratedValue
	 */
	public $id;

	/** @Column(name="first_name") */
	public $firstName = '';

	/** @Column(name="last_name") */
	public $lastName = '';

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
}
