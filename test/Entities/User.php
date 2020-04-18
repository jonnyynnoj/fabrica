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
}
