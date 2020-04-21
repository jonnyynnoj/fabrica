<?php declare(strict_types=1);

namespace Fabrica\Test\Entities;

/** @Entity */
class Address
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	public $id;

	/** @Column(type = "string") */
	public $street;

	/** @Column(type = "string") */
	public $city;

	/** @Column(type = "string") */
	public $country;

	/** @Column(type = "string") */
	public $postCode;

	public function setCity(string $city)
	{
		$this->city = $city;
		return $this;
	}
}
