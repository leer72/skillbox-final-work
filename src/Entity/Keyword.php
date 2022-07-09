<?php

namespace App\Entity;

use App\Repository\KeywordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=KeywordRepository::class)
 */
class Keyword
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $keyword = [];

    /**
     * @ORM\OneToOne(targetEntity=Article::class, mappedBy="keyword", cascade={"persist", "remove"})
     */
    private $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyword(int $num = 0): string
    {
        if ( count($this->keyword) >= $num) {
            
            return $this->keyword[$num];
        }
        
        return $this->keyword[0];
    }
    
    public function setKeyword($keyword): self
    {
        if(is_string($keyword)) {
            $this->keyword[] = $keyword;
        } else {
            $this->keyword = $keyword; 
        }

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        // unset the owning side of the relation if necessary
        if ($article === null && $this->article !== null) {
            $this->article->setKeyword(null);
        }

        // set the owning side of the relation if necessary
        if ($article !== null && $article->getKeyword() !== $this) {
            $article->setKeyword($this);
        }

        $this->article = $article;

        return $this;
    }

    public function __toString()
    {
        return $this->keyword[0];
    }
}
