<?php declare(strict_types=1);

namespace Fabrica\Test\Entities;

/**
 * @Entity
 * @Table(name="posts")
 */
class Post
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	public $id;

	/** @ManyToOne(targetEntity="Fabrica\Test\Entities\User", inversedBy="posts", cascade={"persist", "remove"}) */
	public $user;

	/** @Column */
	public $title;

	/** @Column */
	public $body;
}
