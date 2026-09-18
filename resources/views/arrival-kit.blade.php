<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ирэх өдрийн багц | {{ config('app.name') }}</title>
    <meta name="description" content="Франкфуртын нисэх буудалд буусны дараах алхам, тасалбар, яаралтай утас, хэрэглээний герман хэллэг. Интернэтгүй үед ч нээгдэнэ.">
    <meta name="theme-color" content="#0b0c0e">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/site.webmanifest">
    {{-- Бүх загвар энэ файл дотор: service worker нэг хуудсыг хадгалахад бүтнээрээ интернэтгүй ажиллана. --}}
    <style>
        :root { --ink: #1b1b19; --muted: #5f5f59; --faint: #8f8e87; --line: #e4e4e0; --paper: #fff; --board: #0b0c0e; --board-line: #272a2f; --signal: #ffb81c; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper); color: var(--ink); font: 16px/1.6 "IBM Plex Sans", -apple-system, "Segoe UI", Roboto, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .mono { font-family: "IBM Plex Mono", ui-monospace, Menlo, Consolas, monospace; font-variant-numeric: tabular-nums; }
        .wrap { max-width: 760px; margin: 0 auto; padding: 0 20px; }
        .kicker { font: 500 11px/1 "IBM Plex Mono", ui-monospace, monospace; letter-spacing: .14em; text-transform: uppercase; color: var(--faint); }
        header.board { background: var(--board); color: #fff; padding: 22px 0 30px; }
        header.board .kicker { color: rgba(255,255,255,.5); }
        .logo { display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
        .logo .t { display: inline-flex; width: 18px; height: 26px; align-items: center; justify-content: center; border-radius: 2.5px; background: linear-gradient(#34363a 0 50%, #28292d 50% 100%); color: var(--signal); font: 600 16px/1 "IBM Plex Mono", ui-monospace, monospace; }
        .logo .n { font: 600 18px/1 "IBM Plex Mono", ui-monospace, monospace; }
        h1 { font-size: 34px; line-height: 1.1; letter-spacing: -.02em; margin: 26px 0 12px; }
        h2 { font-size: 21px; letter-spacing: -.01em; margin: 0 0 14px; }
        header p { color: rgba(255,255,255,.65); margin: 0; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .btn { display: inline-flex; align-items: center; height: 44px; padding: 0 18px; border-radius: 4px; font: 600 15px/1 inherit; text-decoration: none; cursor: pointer; border: 0; }
        .btn-signal { background: var(--signal); color: var(--ink); }
        .btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.25); }
        .status { display: none; margin-top: 16px; padding: 10px 14px; border: 1px solid var(--board-line); color: var(--signal); font-size: 14px; }
        .status.show { display: block; }
        section { padding: 34px 0; border-bottom: 1px solid var(--line); }
        ol.steps { list-style: none; margin: 0; padding: 0; counter-reset: s; }
        ol.steps li { position: relative; padding: 0 0 16px 44px; counter-increment: s; }
        ol.steps li::before { content: counter(s, decimal-leading-zero); position: absolute; left: 0; top: 1px; font: 500 13px/1.6 "IBM Plex Mono", ui-monospace, monospace; color: var(--faint); }
        ol.steps b { display: block; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 11px 0; border-top: 1px solid var(--line); vertical-align: top; }
        td:first-child { padding-right: 16px; }
        .num { font: 600 20px/1.3 "IBM Plex Mono", ui-monospace, monospace; white-space: nowrap; }
        a.num { color: var(--ink); text-decoration: none; }
        .de { font-weight: 600; }
        .mn { color: var(--muted); }
        .note { border: 1px solid #fde68a; border-left: 3px solid #f59e0b; background: #fffbeb; padding: 12px 14px; font-size: 15px; }
        .card { border: 1.5px dashed var(--ink); padding: 18px; }
        .field { border-bottom: 1px solid var(--ink); height: 34px; margin-bottom: 14px; }
        .field-label { font-size: 13px; color: var(--muted); }
        footer { padding: 28px 0 48px; color: var(--faint); font-size: 14px; }
        footer a { color: var(--ink); }
        @media print {
            header.board { background: #fff; color: var(--ink); padding: 0 0 12px; border-bottom: 2px solid var(--ink); }
            header.board p, header.board .kicker { color: var(--muted); }
            .logo { color: var(--ink); }
            .actions, .status, .no-print { display: none !important; }
            section { padding: 16px 0; break-inside: avoid; }
            body { font-size: 13px; }
            h1 { font-size: 24px; margin: 10px 0 6px; }
        }
    </style>
</head>
<body>
    <header class="board">
        <div class="wrap">
            <a class="logo" href="/" aria-label="{{ config('app.name') }}"><span class="t">O</span><span class="t">M</span><span class="n">137</span></a>
            <h1>Ирэх өдрийн багц</h1>
            <p>Франкфуртын нисэх буудалд буусны дараа хэрэг болох мэдээлэл. Энэ хуудас нэг удаа нээгдсэний дараа утсанд хадгалагдаж, интернэтгүй үед ч нээгдэнэ.</p>
            <div class="actions">
                <button type="button" class="btn btn-signal" onclick="window.print()">Хэвлэх</button>
                <a class="btn btn-ghost" href="/guides/frankfurtyn-nisex-buudlaas-xot-ruu">Дэлгэрэнгүй заавар</a>
            </div>
            <div id="offline" class="status">Та интернэтгүй байна. Энэ хуудас утсанд хадгалагдсан хувилбараас нээгдлээ.</div>
            <div id="saved" class="status">Энэ хуудас утсанд хадгалагдлаа. Интернэтгүй үед ч нээгдэнэ.</div>
        </div>
    </header>

    <main class="wrap">
        <section>
            <p class="kicker">01 · Онгоцноос буусны дараа</p>
            <h2>Терминал 3-аас хот руу</h2>
            <ol class="steps">
                <li><b>Терминал 3-т буух</b>МИАТ Терминал 3-т үйлчилдэг. Паспортын хяналт, ачаа авах, гаалийн хяналтыг дараалан дамжина. Мэдүүлэх зүйлгүй бол ногоон, мэдүүлэх зүйлтэй бол улаан гарцаар гарна.</li>
                <li><b>SkyLine галт тэргээр Терминал 1 руу</b>Үнэгүй, 2-3 минут тутамд явна. 04:00-23:00 цагийн хооронд ажиллана, шөнө оронд нь автобус явна.</li>
                <li><b>S8, S9 галт тэргээр төв буудал руу</b>Терминал 1-ийн доорх бүсийн галт тэрэгний буудлаас (Regionalbahnhof) Франкфуртын төв буудал (Hauptbahnhof) хүртэл 15 орчим минут.</li>
                <li><b>Бусад хот руу</b>Холын галт тэрэгний буудлаас (Fernbahnhof) ICE галт тэргээр Берлин, Мюнхен, Кёльн зэрэг хот руу шууд явна.</li>
            </ol>
        </section>

        <section>
            <p class="kicker">02 · Тасалбар</p>
            <h2>Тасалбар авах</h2>
            <p>Буудлын автомат машинаас бэлэн мөнгө эсвэл картаар, эсвэл RMV (Франкфурт орчим), DB Navigator (Герман даяар) аппаас авна.</p>
            <p>Удаан хугацаагаар амьдрах бол Deutschlandticket нь сард 63 евро бөгөөд хот доторх нийтийн тээвэр, бүсийн галт тэргээр хязгааргүй зорчино. ICE-д хүчингүй.</p>
        </section>

        <section>
            <p class="kicker">03 · Яаралтай утас</p>
            <h2>Яаралтай үед</h2>
            <table>
                <tr><td><a class="num" href="tel:112">112</a></td><td>Түргэн тусламж, гал унтраах. Үнэгүй, 24 цаг.</td></tr>
                <tr><td><a class="num" href="tel:110">110</a></td><td>Цагдаа. Үнэгүй, 24 цаг.</td></tr>
                <tr><td><a class="num" href="tel:+49304748060">+49 30 474 80 60</a></td><td>Монгол Улсын ЭСЯ, Берлин (Hausvogteiplatz 14)</td></tr>
                <tr><td><a class="num" href="tel:+493047480620">+49 30 47 48 06 20</a></td><td>ЭСЯ-ны консулын хэлтэс</td></tr>
            </table>
        </section>

        <section>
            <p class="kicker">04 · Хэрэгтэй хэллэг</p>
            <h2>Герман хэллэг</h2>
            <table>
                <tr><td class="de">Entschuldigung, sprechen Sie Englisch?</td><td class="mn">Уучлаарай, та англиар ярьдаг уу?</td></tr>
                <tr><td class="de">Wo ist der Bahnhof?</td><td class="mn">Галт тэрэгний буудал хаана байна вэ?</td></tr>
                <tr><td class="de">Eine Fahrkarte zum Hauptbahnhof, bitte.</td><td class="mn">Төв буудал хүртэл нэг тасалбар өгнө үү.</td></tr>
                <tr><td class="de">Ich brauche Hilfe.</td><td class="mn">Надад тусламж хэрэгтэй байна.</td></tr>
                <tr><td class="de">Mein Gepäck ist nicht angekommen.</td><td class="mn">Миний ачаа ирээгүй байна.</td></tr>
                <tr><td class="de">Ich verstehe nicht.</td><td class="mn">Би ойлгохгүй байна.</td></tr>
                <tr><td class="de">Können Sie das bitte aufschreiben?</td><td class="mn">Үүнийг бичиж өгөхгүй юу?</td></tr>
                <tr><td class="de">Wie viel kostet das?</td><td class="mn">Энэ хэд вэ?</td></tr>
                <tr><td class="de">Ich möchte zu dieser Adresse.</td><td class="mn">Би энэ хаяг руу явмаар байна.</td></tr>
            </table>
        </section>

        <section>
            <p class="kicker">05 · Гааль</p>
            <h2>Гаалийн дүрэм</h2>
            <div class="note">Европын холбооны гаднаас мах, сүүн бүтээгдэхүүн (борц, ааруул, бяслаг гэх мэт) авч орохыг хориглодог. 10 000 евро буюу түүнээс дээш бэлэн мөнгийг гаальд заавал мэдүүлнэ.</div>
        </section>

        <section>
            <p class="kicker">06 · Хаягийн карт</p>
            <h2>Хэвлээд бөглөж аваарай</h2>
            <p class="no-print mn">Такси, галт тэрэгний ажилтанд үзүүлэхэд хэрэгтэй.</p>
            <div class="card">
                <div class="field-label">Очих хаяг / Zieladresse</div><div class="field"></div><div class="field"></div>
                <div class="field-label">Хүлээж авах хүн / Kontaktperson</div><div class="field"></div>
                <div class="field-label">Утас / Telefon</div><div class="field"></div>
            </div>
        </section>
    </main>

    <footer class="wrap">
        <p>Сүүлд шалгасан: 2026.09.18. Мэдээлэл өөрчлөгдөж болох тул албан ёсны эх сурвалжаас шалгаарай.</p>
        <p>{{ config('app.name') }} нь хараат бус сайт бөгөөд МИАТ болон бусад агаарын тээврийн компанитай холбоогүй.</p>
        <p><a href="/">{{ preg_replace('#^https?://#', '', config('app.url')) }}</a></p>
    </footer>

    <script>
        (function () {
            if (!navigator.onLine) document.getElementById('offline').classList.add('show');
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').then(function () {
                    return navigator.serviceWorker.ready;
                }).then(function () {
                    if (navigator.onLine) document.getElementById('saved').classList.add('show');
                }).catch(function () {});
            }
        })();
    </script>
</body>
</html>
