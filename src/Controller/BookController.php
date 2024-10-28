<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BookRepository;
use App\Entity\Book;
use App\Form\BookType;

// #[Route('book')]
class BookController extends AbstractController
{
   

    #[Route('/bookList', name: 'app_book_list', methods: ['GET'])]
public function bookList(BookRepository $bookRepo): Response
{
    // Récupérer la liste des livres publiés
    $publishedBooks = $bookRepo->findBy(['published' => true]);
    
    // Récupérer la liste des livres non publiés
    $unpublishedBooks = $bookRepo->findBy(['published' => false]);

    // Compter les livres publiés et non publiés
    $countPublished = count($publishedBooks);
    $countUnpublished = count($unpublishedBooks);

    return $this->render('book/bookList.html.twig', [
        'books' => $publishedBooks,
        'countPublished' => $countPublished,
        'countUnpublished' => $countUnpublished,
    ]);
}


    #[Route('/add', name: 'app_add_book')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $book = new Book();
        $book->setPublished(true); // Initialiser published à true

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Incrémentation de nb_books de l'auteur
            $author = $book->getAuthor(); // Assurez-vous que le livre a un auteur
            if ($author) {
                $author->setNb_Books($author->getNb_Books() + 1);
            }
            $em=$doctrine->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('app_book_list'); // Redirection après ajout
        }

        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/edit/{id}', name: 'app_update_book')]
public function update(Request $request, BookRepository $bookRepo, Book $book,ManagerRegistry $doctrine): Response
{
   
    
    $form = $this->createForm(BookType::class, $book);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $em=$doctrine->getManager();
      
        $em->flush();
        

        return $this->redirectToRoute('app_book_list');
    }

    return $this->render('book/update.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/delete/{id}', name: 'app_delete_book')]
public function delete(BookRepository $bookRepo, Book $book,ManagerRegistry $doctrine): Response
{
    

    if ($book) {
        $em=$doctrine->getManager();
        $em->remove($book);
        $em->flush();
    }

    return $this->redirectToRoute('app_book_list');
}

//1) Atelier DQL
#[Route('/countRomanceBooks', name: 'app_count_romance_books', methods: ['GET'])]
public function countRomanceBooks(ManagerRegistry $doctrine): Response
{
    // Écrire une requête DQL pour compter les livres dans la catégorie « Romance »
    $em = $doctrine->getManager();
    $query = $em->createQuery(
        'SELECT COUNT(b.id) FROM App\Entity\Book b WHERE b.category = :category'
    )->setParameter('category', 'Romance');

    // Exécuter la requête et obtenir le résultat
    $countRomanceBooks = $query->getSingleScalarResult();

    return $this->render('book/countRomanceBooks.html.twig', [
        'countRomanceBooks' => $countRomanceBooks,
    ]);
}

//2) Atelier DQL
#[Route('/booksBetweenDates', name: 'app_books_between_dates', methods: ['GET'])]
public function booksBetweenDates(ManagerRegistry $doctrine): Response
{
    $query = $doctrine->getManager()->createQuery(
        'SELECT b FROM App\Entity\Book b WHERE b.publicationDate BETWEEN :startDate AND :endDate'
    )->setParameters([
        'startDate' => '2014-01-01',
        'endDate' => '2018-12-31'
    ]);

    // Get the list of books published between the two dates
    $books = $query->getResult();

    // Pass the list of books to the Twig template
    return $this->render('book/books_between_dates.html.twig', [
        'books' => $books
    ]);
}




}
