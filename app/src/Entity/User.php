<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users_table")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Doctrine\CustomIdGenerator")
     * @ORM\Column(type="string", length=10)
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(
     *     message = "name must be not blank",
     * )
     * @Assert\NotNull(
     *     message = "name must be not null",
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\Email(
     *     message = "email '{{ value }}' is not a valid email."
     * )
     * @Assert\NotBlank(
     *     message = "email must be not blank",
     * )
     * @Assert\NotNull(
     *     message = "email must be not null",
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "email cannot be longer than {{ limit }} characters"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(
     *     message = "location must be not blank",
     * )
     * @Assert\NotNull(
     *     message = "location must be not null",
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "location cannot be longer than {{ limit }} characters"
     * )
     */
    private $location;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }
}
