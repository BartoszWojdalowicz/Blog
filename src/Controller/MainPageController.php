<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;



class MainPageController extends AbstractController
{
    /**
     * @Route("/mainPage", name="main_page")
     */
    public function index(ArticleRepository $articleRepository,PaginatorInterface $paginator,Request $request)
    {

        $pagination = $paginator->paginate(
            $articleRepository->findArticleToPaginator(),
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('main_page/index.html.twig',
            ['pagination' => $pagination]);
    }
}
