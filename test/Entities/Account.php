<?php declare(strict_types=1);

namespace Fabrica\Test\Entities;

/**
 * @Entity
 * @Table(name="accounts")
 */
class Account
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	public $id;

	/** @OneToOne(targetEntity="Fabrica\Test\Entities\Address", cascade={"persist"}) */
	public $address;

	public function getAddress(): Address
	{
		return $this->address;
	}
}
