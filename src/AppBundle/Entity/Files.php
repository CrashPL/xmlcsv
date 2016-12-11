<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;



/**
 * Files
 *
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FilesRepository")
 */
class Files
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @Assert\File(maxSize="6000000")
     */
    private $file;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;
    
    private $filename;

    /**
     * 
     * @return type
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * 
     * @param type $path
     * 
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
                
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads';
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * 
     * @return type
     */
    public function upload($extension = null)
{
        
    // zmienna file może być pusta jeśli pole nie jest wymagane
    if (null === $this->file) {
        return;
    }
    
    if($extension === null)
    {
        $extension = $this->file->guessExtension();
    }
    // używamy oryginalnej nazwy pliku ale nie powinieneś tego robić
    // aby zabezpieczyć się przed ewentualnymi problemami w bezpieczeństwie

    // metoda move jako atrybuty przyjmuje ścieżkę docelową gdzie trafi przenoszony plik
    // oraz ścieżkę z której ma przenieś plik
    $this->file->move($this->getUploadRootDir(), $this->filename . '.' . $extension);


    
    // wyczyść zmienną file ponieważ już jej nie potrzebujemy
    $this->file = null;
}

    /**
     * @ORM\PrePersist()
     */
    public function preUpload($extension = null)
    {        if  (null !== $this->file){
            // zrób cokolwiek chcesz aby wygenerować unikalną nazwę
            // ustaw zmienną patch ścieżką do zapisanego pliku
            
            if($extension === null)
            {
                $extension = $this->file->guessExtension();
            }
            
            $this->filename = sha1(uniqid(mt_rand(), true));
            $this->setPath($this->filename . '.' . $extension);
        }
       
    }
    
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateUpload($extension = null)
    {
        if($extension === null)
        {
            $extension = $this->file->guessExtension();
        }
        
         if($this->file !== null){
            $this->removeUpload();
            $this->filename = sha1(uniqid(mt_rand(), true));
            $this->setPath($this->filename . '.' . $extension);
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setFile($file)
    {
       $this->file = $file;
       return $this;
    }
}
