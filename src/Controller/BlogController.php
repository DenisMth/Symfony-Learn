<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;


use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Form\ArticleType;

class BlogController extends AbstractController{

    #[Route('/', name: 'blog_home')]
    public function home(ArticleRepository $repo):Response
    {

        $articles = $repo->findLastArticles(3);

        return $this->render('blog/home.html.twig', ['articles' => $articles,]);
    }

    #[Route('/articles', name: 'all_articles')]
    public function allArticles(ArticleRepository $repo):Response
    {

        $articles = $repo->findAll();

        return $this->render('blog/articles.html.twig', ['articles' => $articles,]);
    }

    #[Route('/article/new', name: 'new_article')]
    #[Route('/article/{id}/edit', name: 'edit_article')]
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager):Response
    {
        if(!$article){
            $article = new Article();
        }
        
        $formArticle = $this->createForm(ArticleType::class, $article);

        $formArticle->handleRequest($request);

        if($formArticle->isSubmitted() && $formArticle->isValid()){
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTimeImmutable());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/new_article.html.twig', [
            'formArticle' => $formArticle->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function showArticle(Article $article):Response
    {
        return $this->render('blog/show_article.html.twig', ['article' => $article]);
    }
}
