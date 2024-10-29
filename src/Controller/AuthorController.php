<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Author;


class AuthorController extends AbstractController
{
 

    #[Route('/author', name: 'app_author', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('author/index.html.twig');
    }

    #[Route('/showAuthor/{name}', name: 'app_showAuthor', defaults:['name'=>'victor hugo'], methods:['GET'])]
    public function showAuthor($name): Response
    {
        return $this->render('author/showAuthor.html.twig', [
            'name' => $name
        ]);
    }

    //Atelier DQL
    #[Route('/authorList', name: 'app_authorList', methods: ['GET'])]
    public function AuthorList(Request $request, AuthorRepository $authorRepo): Response
    {
        // Initialize the query builder to fetch authors
        $query = $authorRepo->createQueryBuilder('a');
    
        // Get search parameters from the request
        $minBooks = $request->query->get('min_books');
        $maxBooks = $request->query->get('max_books');
    
    
        // Check if at least one search parameter is provided
        $isSearching = false;
    
        
    
        // Filter by number of books if both min and max are provided
        if ($minBooks !== null && $maxBooks !== null) {
            $query->andWhere('a.nb_books BETWEEN :minBooks AND :maxBooks')
                  ->setParameter('minBooks', $minBooks)
                  ->setParameter('maxBooks', $maxBooks);
            $isSearching = true; // We are performing a search
        }
    
        // Execute the query to get the filtered authors
        $authors = $query->getQuery()->getResult();
    
        // If no filters are applied, fetch all authors if needed
        if (!$isSearching) {
            $authors = $authorRepo->findAll(); // Fetch all authors
        }
    
        // Render the view with the list of authors
        return $this->render('author/authorList.html.twig', [
            'authors' => $authors,
        ]);
    }
    

    #[Route('/author/details/{id}', name: 'author_details', methods: ['GET'])]
    public function authorDetails(int $id, AuthorRepository $authorRepo): Response
    {
        // Récupérer l'auteur en fonction de l'ID en utilisant le repository
        $author = $authorRepo->findAuthorById($id); 

        // Rendre la vue avec les détails de l'auteur
        return $this->render('author/details.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/addAuthor', name: 'addAuthor', methods: ['GET'])]
    public function addAuthor(ManagerRegistry $doctrine): Response{
         $author=new Author();
        $author->setUsername('ahmed');
        $author->setEmail('ahmed@esprit.tn');
        $author->setPicture('ahmed.png');
        
        $author->setNb_Books(50);
         //2.create a copy of the doctrine with entityManager: $em
        //2.a: use Doctrine\Persistence\ManagerRegistry;
        $em=$doctrine->getManager();
         //3.save data: object in the database
        $em->persist($author);
        $em->flush();
        return $this->redirectToRoute('app_authorList');

    }

    #[Route('/deleteAuthor/{id}', name: 'deleteAuthor', methods: ['GET','DELETE'])]
    public function deleteAuthor(Author $author,ManagerRegistry $doctrine):Response{
        if($author){
         //2.create a copy of the doctrine with entityManager: $em
        //2.a: use Doctrine\Persistence\ManagerRegistry;
        $em=$doctrine->getManager();
        //3. remove the object from the doctrine layer
        $em->remove($author);
        //4. save the updates in the database
        $em->flush();
        }
        return $this->redirectToRoute('app_authorList');

    }

    #[Route('/updateAuthor/{id}', name: 'updateAuthor')]
    public function updateAuthor(Author $author,ManagerRegistry $doctrine):Response{
        if($author){
        // $author=$this->authorRepo->findAuthorById($id);
       
        $author->setUsername("Bonjour");
        $em=$doctrine->getManager();
        $em->remove($author);
        $em->flush();
        }
        return $this->redirectToRoute('app_authorList');

    }

//4) DQL
#[Route('/delete-authors-with-no-books', name: 'delete_authors_with_no_books')]
public function deleteAuthorsWithNoBooks(AuthorRepository $authorRepo): Response
{
    // Call the repository method to delete authors with no books
    $deletedCount = $authorRepo->deleteAuthorsWithNoBooks();

    // Add a flash message or some feedback if necessary
    $this->addFlash('success', "$deletedCount authors deleted.");

    // Redirect back to the author list page
    return $this->redirectToRoute('app_authorList');
}

//1) Atelier Query Builder
#[Route('/authorListByEmail', name: 'app_authorListByEmail', methods: ['GET'])]
public function AuthorListByEmail(AuthorRepository $authorRepo): Response
{
    // Fetch authors ordered by email
    $authors = $authorRepo->listAuthorByEmail(); 

    // Render the view with the sorted list of authors
    return $this->render('author/authorListByEmail.html.twig', [
        'authors' => $authors,
    ]);
}
    
}
