[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/abb7pIlM)

# Taquilla Online — Pràctica PHP

## 1. Connexió a la Base de Dades

S'implementa el patró Singleton dins de l'arxiu db.php per tal de gestionar la connexió des d'un mateix lloc a la base de dades. Aquest patró garanteix que nomès es pugui crear una instància ja que el constructor és privat i s'hi accedeix mitjançant un mètode estàtic. A més s'han afegit dos mètodes extres per tal d'evitar la clonació i la deserialització.

## 2. Recuperació de la Comanda Pendent

Per tal de recuperar la comanda pendent de l'usuari, s'executa una consulta que busca dins de la taula l'última comanda amb estat pending, el qual està associat al mail de l'usuari actual. Es fa una consulta SQL per tal d'obtenir-lo, i amb això sabem quina es la comanda pendent per tal de poder obtenir els ítems associats amb la taula tickets_types.

## 3. Ejemplo de Consulta con Prepared Statement

Una consulta amb prepared statement evita la sql injection:

```php
<?php
$placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
$stmt = $conn->prepare("SELECT id, price FROM ticket_types WHERE id IN ($placeholders)");
$stmt->execute($ticket_ids);
```

Es construeixen dinàmicament els placeholders segons la quantitat d'IDs que es volen extreure de la consulta SQL, i es passen com un array a execute. Això fa la consulta segura fins i tot amb entrades d'usuari, ja que aquestes es poden controlar abans d'enviar-les a la base de dades.

## 4. Enlace al vídeo demostración

recording-2025-11-03-19-22-16_Edit.mp4 <https://reccloud.com/es/u/e2ue4da>
