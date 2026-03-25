<?php

namespace App\Application\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'etudiant')]
class etudiant
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100)]
    private string $prenom;

    #[Column(type: 'string', length: 100)]
    private string $nom;

    #[Column(type: 'string', length: 20)]
    private string $numeroTelephone;

    #[Column(type: 'string', length: 10)]
    private string $genre;

    #[Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[Column(type: 'string', length: 255)]
    private string $motDePasse;

    #[Column(type: 'date_immutable')]
    private DateTimeImmutable $dateNaissance;

    #[Column(type: 'string', length: 50)]
    private string $promo;



    public function __construct(
        string $prenom,
        string $nom,
        string $numeroTelephone,
        string $genre,
        string $email,
        string $motDePasse,
        DateTimeImmutable $dateNaissance,
        string $promo
    ) {
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->numeroTelephone = $numeroTelephone;
        $this->genre = $genre;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->dateNaissance = $dateNaissance;
        $this->promo = $promo;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getNumeroTelephone(): string
    {
        return $this->numeroTelephone;
    }

    public function getGenre(): string
    {
        return $this->genre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function getDateNaissance(): DateTimeImmutable
    {
        return $this->dateNaissance;
    }

    public function getpromo(): string
    {
        return $this->promo;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setNumeroTelephone(string $numeroTelephone): void
    {
        $this->numeroTelephone = $numeroTelephone;
    }

    public function setGenre(string $genre): void
    {
        $this->genre = $genre;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setMotDePasse(string $motDePasse): void
    {
        $this->motDePasse = $motDePasse;
    }

    public function setDateNaissance(DateTimeImmutable $dateNaissance): void
    {
        $this->dateNaissance = $dateNaissance;
    }

    public function setPromo(string $promo): void
    {
        $this->promo = $promo;
    }
}