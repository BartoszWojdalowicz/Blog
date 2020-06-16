# Blog
Blog powstał w celu ćwiczeń zagadnień związanych z progrmowaniem w php we frameworku symfony.
są tam zaimplementowane takie funkcjonalności jak: 
- logowanie/rejestracja
- resetowanie hasła + prosty sposob na wygaśnięcie linku po godzinie od wygenerowania 
- email potwierdzający rejestrację
- paginacja postów na stornie głównej
- lajkowanie po wejściu w dany artykuł 
- dodawanie komentarzy po wejściu w artykuł 
- lista wszystkich artykułów z dostępem do edycji oraz usuwania jeśli jest się właścicielem. 
- upload zdjęcia do artykułu
- dynamiczne tagi w artykułach (po wyborze jednego z 2 głównych pojawiają się odpowiednie pochodne tagi.
wybory są generowane na podstawie tabeli z bazy danych (EntityFormType) przez co może nie działać przy pustym projekcie)
- zaimplementowany został edytor WYSIWYG CKEditor dzięki czemu można formatować pisane artykuły.
- całość została w miare możliwości stworzona w MVC, wszelkie większe zapytania do bazy danych znajdują się w repozytoriach, szablony w plikach twig, a logika w kontrolerach.

projekt powstał na szybko w celu powtórzenia i utrwalenia zdobytej wiedzy przez co nie zachwyca on pięknym CSS oraz nie jest projektem najwyższych lotów
