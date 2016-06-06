<?php
/**
 * Sample layout.
 */
use Core\Language;

?>
<div style="margin:20px">
<B>Krok 1</B>: W zakładce "Pobieranie Zaawansowane" zdefinuj stronę internetową, którą chcesz pobierać.<BR>
<img src="tutorial/1.jpg"><BR>
Zdefiniuj adres strony i (opcjonalnie) nadaj nazwę projektu. W przypadku nie podania nazwy projekt otrzyma domyślną nazwę.<BR>
Zatwierdź pobieranie przy pomocy przycisku. Pobieranie rozpocznie się od razu i będzie trwać przez parę godzin.<BR>
<img src="tutorial/2.jpg"><BR>
W tym czasie możesz mieć zamkniętą przeglądarkę, ale zalecane jest pozostawienie jej otwartej. Skróci to czas otwierania projektu potem.<BR>
Przycisk "Idź do edycji" aktualnie jest wadliwy, skorzystaj z linku na górze strony.<BR>
<img src="tutorial/3.jpg"><BR>
<B>Krok 2</B>: W zakładce "Ekstrakcja Wzorca" zdefiniuj wzorec pobierania dla swojego projektu.<BR>
<img src="tutorial/4.jpg"><BR>
Korzystając z menu na środku wybierz swój projekt. Otwieranie projektu może zająć trochę czasu, czasem są to projekty o rozmiarze paru gigabajtów!<BR>
Po otwarciu możesz wybrać podstronę, według której będziesz wyznaczać wzorzec. Możesz otworzyć jej podgląd korzystając z odpowiedniego przycisku. Gdy już się zdecydujesz, zatwierdź przyciskiem "Wczytaj drzewo strony".<BR>
<img src="tutorial/55.jpg"><BR>
Przed sobą będziesz mieć widok drzewa strony. W menu po prawej masz przyciski służące do zaznaczania elementów na stronie. Po kliknięciu w dowolny będziesz mógl zaznaczyć wybrany element drzewa. Gdy jest możliwe zaznaczenie elementu drzewa, podświetli się na kolorze zielonym<BR>
<img src="tutorial/6.jpg"><BR>
Gdy nie ma możiwości zaznaczenia elementu drzewa, podświetli się na czerwono<BR>
<img src="tutorial/7.jpg"><BR>
Pamiętaj o hierarchii elementów! Notka jest elementem najważniejszym, ona ma opisywać wszystkie pozostałe elementy wpisu, jak autor, tytuł, data i treść. Pozostałe sytuacje są dopuszczalne. Pamiętaj! Jeżeli element podświetla się na zielono, to znaczy, że akcja jest dopuszczalna!<BR>
Jak widzisz, przyciski po prawej są oznaczone kolorami. Takie same kolory będziesz widział, jak oznaczysz te elementy w drzewie. W przypadku, gdy tym samym kolorem oznaczysz ten same element drzewa (gdyż np. data i tytuł znajdują się w tym samym miejscu) zamiast jednego koloru zobaczysz gradient.<BR>
Staraj się, aby jak najlepiej opisywać elementy. Jeżeli widzisz, że tytuł znajduje się w tagu nagłówka (h1, h2, h3), postaraj się ten element oznaczyć, nie oznaczaj tekstu, chyba że jest to konieczne. Przykład takiej konieczności może wystąpić, kiedy w jednym pojemniku znajduje się data, tytuł i autor rozdzielone znanikiem "br".<BR>
<img src="tutorial/8.jpg"><BR>
Po skończonym procesie zaznaczania elementów (przykład powyżej) kliknij przycisk "Send Request". Proces ten jest długotrwały i obciążający dla serwera. Postaraj się nie wykonywać żadnych innych czynności, które mogłyby obciążać serwer, to jest: nie wykonuj kolejnych ekstrakcji wzorca. Możesz rozpoczynać kolejne operacje pobierania.<BR>
Jeżeli chciałbyś przeprowadzić badania, jak zostały zdefiniowane w pracy magisterskiej, wypełnij pozostałe pola formularza regułami selektorowymi. Poniżej zrzut ekranu z wypełnionymi polami.<BR>
<img src="tutorial/9.jpg"><BR>
W momencie zakończenia operacji wszystkie przyciski się odblokują a ty otrzymasz spakowany plik ZIP zawierający korpus w formacie Premorph (XML). Skorzystaj z narzędzia jak WCRFT do konwersji do formatu CCL.<BR>
<div style="margin-bottom: 50px">
Miłego korzystania!<BR></div>
</div>