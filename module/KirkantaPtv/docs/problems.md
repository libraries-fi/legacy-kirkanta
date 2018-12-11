Organisaatiot
=============

Puuttuvat kentät, epämääräiset tiedot
----------------

Kenttä | Selite
------ | ------
id | Tietueen tunnus PTV:ssä
oid | Sisäinen organisatiotunnus?
businessCode | Y-tunnus


Tietotyyppien aiheuttamat konfliktit
----------------------------------

Kenttä | Vaikeustaso | Kuvaus | Mahdollinen ratkaisu
------ | ----------- | ------ | --------------------
organizationDescriptions | matala | Pituus rajoitettu 500 merkkiin ja vaatii plaintextin. Finnan kanssa sovittu aiemmin html-muotoisista kuvailuista ja teksti voi olla reilusti pidempi. | Muotoilut voi riisua ja tekstin katkaista esimerkiksi pisteen kohdalta.
emailAddresses | matala | Nimettyjä sähköpostiosoitteita voi olla useita. **Nimi on kielikohtainen**. Kirkannassa vain yksi monikielinen säpo ilman nimeä |
phoneNumbers | matala | Palvelunumeroiden maksullisuus ilmoitettava | Kirjastoilla ei liene maksullisia numeroita?
phoneNumbers | matala | Puhelinnumerot ilmeisesti kielikohtaisia; Kirkanta näyttää toistaiseksi kaikki numerot kaikille kielille (nimi käännettävissä) | Jo nyt mahdollista filtteröidä pois numerot, joille ei kyseisellä kielellä nimeä. Yhdenmukaisuuden vuoksi käytäntö pitäisi adoptoida myös rajapintaan ja julkiselle puolelle.
addresses | korkea | PTV:n tietorakenne ei salli määritellä postitoimipaikkaa postiosoitteille. Ainoastaan katuosoite sallittu. Todellisuudessa postiosoitteet voivat olla muotoa "PL 123 Helsingin kaupunki" | Toistaiseksi tehty niin, että mikäli katuosoite on Kirkannassa tyhjä, syötetään katuosoitteeksi postitoimipaikka.
addresses | korkea | Dokumentaatio ja rajapinnan palauttama data ristiriidassa keskenään. Ongelmakohtia: koordinaattien määritys, yllä mainittu postitoimipaikan kenttä, lisäksi ylimääräinen kenttä "coordinateState". |
