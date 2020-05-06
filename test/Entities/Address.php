<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Entities;

/** @Embeddable */
class Address
{
	/** @Column(type = "string", nullable = true) */
	public $street;

	/** @Column(type = "string", nullable = true) */
	public $city;

	/** @Column(type = "string", nullable = true) */
	public $country;
}
