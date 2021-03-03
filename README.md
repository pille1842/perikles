# Perikles -- Online-Abstimmungssystem

Perikles ist ein Online-Abstimmungstool, das geheime Abstimmungen über das Internet erlaubt. Auf die folgenden Eigenschaften wurde besonderer Wert gelegt:

- **Stimmen sind geheim.** Es ist auch mit direktem Zugriff auf die Datenbank nicht möglich festzustellen, welcher Wähler für welche Option abgestimmt hat. Es ist auch nicht möglich herauszufinden, welche Wähler sich überhaupt an der Abstimmung beteiligt haben. Dies wird durch eine strikte Trennung von Wählern, Wahlberechtigungsausweisen und abgegebenen Stimmen auf allen Ebenen der Anwendung erreicht.
- **Die korrekte Zählung der Stimmen ist für jeden Wähler überprüfbar.** Mithilfe des in der Wahlbenachrichtigung enthaltenen Passcodes kann jeder Wähler nach Ende der Abstimmung überprüfen, ob seine Stimmabgabe in die Ergebnisberechnung eingeflossen ist. Durch den Einsatz moderner SHA2-Hashingfunktionen ist ausgeschlossen, dass Personen mit Zugriff auf die Datenbank Stimmen löschen oder verändern, ohne dass die betroffenen Wähler dies entdecken können.
- **Perikles ist freie Software.** Der Quellcode ist öffentlich und die oben genannten Eigenschaften sind somit für jeden, der über die nötige Fachkenntnis verfügt, überprüfbar. Jeder kann das Tool an seine eigenen Bedürfnisse anpassen. Der Einsatz von Perikles ist für jeden kostenlos und unbeschränkt nach den Bedingungen der GNU GPL v3 möglich. Für immer.

## Zielgruppe und Motivation
Perikles entstand in der Corona-Krise aus der Not heraus, möglicherweise geheime Abstimmungen bei Online-Vereinssitzungen durchführen zu müssen. Keines der frei verfügbaren Systeme entsprach dabei den Ansprüchen und Wünschen des Autors.

Perikles richtet sich dementsprechend an Privatpersonen und kleine Vereine. Im Abschnitt "Technische und rechtliche Limitationen" wird näher darauf eingegangen, weshalb Perikles kein hundertprozentig sicheres System sein kann und auch keinen Anspruch darauf erhebt. Die App wurde nach bestem Wissen und Gewissen in der Hoffnung erstellt, dass sie in diesen ungewöhnlichen Zeiten nützlich sein kann.

Der Name "Perikles" basiert auf dem gleichnamigen athenischen Philosophen und Staatsmann, der die attische Demokratie im 5. Jahrhundert v. Chr. wesentlich weiterentwickelte.

## Installation
Perikles ist eine PHP-Anwendung basierend auf dem Symfony-Framework (Version 5.2).

### Für Endnutzer
Im Release-Bereich stehen einsatzfertige ZIP-Dateien bereit, die auf einem Webserver entpackt werden können. Nach dem Upload sollten in der ˋ.envˋ-Datei die Konfigurationswerte angepasst werden, insbesondere die Verbindungsinformationen zur Datenbank und zu einem SMTP-Mailserver, über den E-Mails verschickt werden können.

### Für Entwickler
- Download des Quellcodes
- Ausführen von ˋcomposer installˋ und ˋyarn install --forceˋ im Projektverzeichnis
- Erstellen einer ˋ.env.localˋ-Datei und Anpassen der Konfigurationswerte
- Start des eingebauten Webservers mit ˋsymfony serveˋ
- Start des Frontend-Compilers mit ˋyarn run encore dev --watchˋ

Die App sollte dann standardmäßig auf localhost:8000 erreichbar sein.

## Funktionen
Es gibt zwei Personengruppen, die für Perikles relevant sind: **Benutzer** und **Wähler**.

Benutzer können sich mit ihrer E-Mail-Adresse und einem Passwort einloggen und haben damit Zugriff auf administrative Funktionen. Sie können in jedem Fall Abstimmungen anlegen, bearbeiten, starten und beenden. Mit den entsprechenden Berechtigungen ist es ihnen auch erlaubt, Wähler und/oder Benutzer zu verwalten.

Wähler können sich nicht in die Anwendung einloggen. Sie sind in der Datenbank mit einem Namen und einer E-Mail-Adresse hinterlegt, aber sie benötigen kein Passwort. Stattdessen können sie mithilfe des Teilnahmelinks und des Passcodes, die sie per E-Mail erhalten, an Abstimmungen teilnehmen, sobald ein Benutzer sie gestartet hat.

### Beispielhafter Ablauf einer Abstimmung
Benutzer Moritz loggt sich mit seiner E-Mail-Adresse und seinem Passwort ein und legt eine neue Abstimmung an. Der Titel lautet: "Was ist die beste Marmelade?"

Im Wählerverzeichnis stehen Marco, Lucas, Katharina und Isabel. Moritz lässt alle vier an der Abstimmung teilnehmen. Nachdem er die Auswahlmöglichkeiten auf Erdbeere, Himbeere, Orange und Aprikose festgelegt hat, startet er die Abstimmung.

Alle vier zugelassenen Wähler erhalten nun eine E-Mail mit dem Betreff "Wahlbenachrichtigung". Diese enthält einen Link zur Teilnahme und einen persönlichen Passcode, mit dem sie sich auf der Abstimmungsseite ausweisen können.

Lucas klickt in der E-Mail auf den Link und wählt "Erdbeere" als beste Marmelade aus. Er gibt seinen Passcode ein und gibt seine Stimme ab. Mit dem selben Teilnahmelink und Passcode ist nun keine Teilnahme an der Abstimmung mehr möglich.

Moritz wird nach einigen Stunden ungeduldig, denn er kann seine Abstimmung nachträglich nicht mehr bearbeiten und auch das Ergebnis nicht einsehen, bis er die Wahl beendet. Er stoppt die Abstimmung. Alle vier Wähler erhalten nun eine E-Mail, die das Ergebnis und einen Link zur Ergebnisseite enthält.

Lucas klickt auf den Ergebnislink. Zu seinem Unmut hat "Himbeere" die Abstimmung gewonnen. Lucas möchte überprüfen, ob seine Stimme ordnungsgemäß gezählt wurde. Er gibt auf der Ergebnisseite seinen Passcode aus der Wahlbenachrichtigung ein. Aus dem Passcode wird ein Hash gebildet, der mit seiner Stimme in der Datenbank verknüpft ist. Das System findet die entsprechende Stimme und meldet Lucas, dass seine Stimme für "Erdbeere" gezählt wurde. Lucas ist beruhigt.

## Erläuterungen zu Passcodes
Passcodes werden bei der Erstellung von Wahlbenachrichtigungen erzeugt und per E-Mail an Wähler verschickt. Sie werden jedoch nur als Hashwert in der Datenbank gespeichert. Es ist trivial, aus dem Passcode im Klartext den Hashwert zu berechnen. Der umgekehrte Weg ist jedoch praktisch ausgeschlossen. Das bedeutet, dass auch ein Benutzer mit direktem Zugriff auf die Datenbank nicht für andere Wähler abstimmen kann.

Bei der Stimmabgabe gibt der Wähler seinen Passcode ein. Aus diesem wird erneut ein anderer Hashwert berechnet, der mit der Stimme zusammen in der Datenbank abgelegt wird. Der Passcode im Klartext wird hingegen nicht gespeichert. Würde die Stimme aus der Datenbank gelöscht oder die gewählte Abstimmungsoption verändert, könnte der betroffene Wähler dies nach Ende der Abstimmung feststellen.

## Technische und rechtliche Limitationen
Perikles erhebt keinen Anspruch darauf, ein vollkommen sicheres System zu sein. Der Quellcode wurde von keiner unabhängigen Instanz überprüft. Ohnehin ist es für einen Wähler ohne Zugriff auf das Innenleben des Servers, auf dem eine Perikles-Installation läuft, unmöglich festzustellen, ob der Quellcode vom Betreiber verändert wurde. Dies ist ein Problem aller elektronischen Abstimmungssysteme.

Perikles richtet sich deshalb an Privatpersonen und kleine Vereine, die auf der Basis von Vertrauen arbeiten können. In keinem Fall ist Perikles ein Ersatz für rechtlich bindende Abstimmungen, die bspw. per Brief abgehalten werden. Es kann keine Haftung für Schäden übernommen werden, die aus der Nutzung von Perikles entstehen.
