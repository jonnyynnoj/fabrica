<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Address
{
	#[ORM\Column(type: "string", nullable: true)]
	public $street;

	#[ORM\Column(type: "string", nullable: true)]
	public $city;

	#[ORM\Column(type: "string", nullable: true)]
	public $country;
}
