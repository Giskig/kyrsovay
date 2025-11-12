-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 12 2025 г., 22:32
-- Версия сервера: 5.7.39
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `smena`
--

-- --------------------------------------------------------

--
-- Структура таблицы `archive`
--

CREATE TABLE `archive` (
  `archive_id` int(11) NOT NULL,
  `id_news` int(11) NOT NULL,
  `reason` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `archive`
--

INSERT INTO `archive` (`archive_id`, `id_news`, `reason`, `date`) VALUES
(1, 8, 'не хочу', '2025-11-01');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `categories_id` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`categories_id`, `title`, `text`) VALUES
(1, 'Образование', 'Новости образовательных программ'),
(2, 'Мероприятия', 'События и мероприятия лагеря'),
(3, 'Спорт', 'Спортивные достижения и события'),
(4, 'Творчество', 'Творческие проекты и выставки');

-- --------------------------------------------------------

--
-- Структура таблицы `changing`
--

CREATE TABLE `changing` (
  `id_changing` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_news` int(11) NOT NULL,
  `date_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `changing`
--

INSERT INTO `changing` (`id_changing`, `id_user`, `id_news`, `date_time`) VALUES
(1, 4, 6, '2025-11-01 09:19:26'),
(2, 4, 7, '2025-11-01 15:20:25'),
(3, 5, 8, '2025-11-01 15:22:19'),
(4, 5, 8, '2025-11-01 15:32:54'),
(5, 5, 8, '2025-11-01 15:34:14'),
(6, 4, 8, '2025-11-01 15:50:51'),
(7, 5, 9, '2025-11-01 15:53:02'),
(8, 4, 9, '2025-11-01 15:53:24'),
(9, 4, 6, '2025-11-01 16:53:36'),
(10, 5, 10, '2025-11-02 17:38:05'),
(11, 5, 11, '2025-11-02 17:38:36'),
(12, 5, 17, '2025-11-02 17:58:27'),
(13, 5, 8, '2025-11-02 17:58:50'),
(14, 5, 8, '2025-11-02 18:01:11'),
(15, 5, 8, '2025-11-02 18:01:19'),
(16, 4, 18, '2025-11-02 18:20:38'),
(17, 4, 17, '2025-11-02 19:35:19'),
(18, 4, 17, '2025-11-02 19:37:33'),
(19, 4, 18, '2025-11-02 19:37:45'),
(20, 4, 9, '2025-11-02 19:37:57'),
(21, 5, 19, '2025-11-02 21:09:27'),
(22, 4, 20, '2025-11-11 19:43:18');

-- --------------------------------------------------------

--
-- Структура таблицы `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `entry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `login_history`
--

INSERT INTO `login_history` (`id`, `id_user`, `entry_date`) VALUES
(1, 4, '2025-11-01'),
(2, 4, '2025-11-01'),
(3, 6, '2025-11-01'),
(4, 4, '2025-11-01'),
(5, 5, '2025-11-01'),
(6, 4, '2025-11-01'),
(7, 5, '2025-11-01'),
(8, 4, '2025-11-01'),
(9, 5, '2025-11-01'),
(10, 4, '2025-11-01'),
(11, 5, '2025-11-01'),
(12, 4, '2025-11-01'),
(13, 5, '2025-11-02'),
(14, 6, '2025-11-02'),
(15, 4, '2025-11-02'),
(16, 5, '2025-11-02'),
(17, 4, '2025-11-02'),
(18, 4, '2025-11-07'),
(19, 5, '2025-11-07'),
(20, 4, '2025-11-07'),
(21, 4, '2025-11-08'),
(22, 4, '2025-11-09'),
(23, 4, '2025-11-10'),
(24, 4, '2025-11-11');

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id_nwes` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_relise` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id_nwes`, `id_user`, `categories_id`, `id_status`, `role_id`, `title`, `text`, `date_relise`) VALUES
(6, 4, 2, 3, 1, 'zxcvb', 'zxcvbnm,смитьб, ывапролдж.', '2025-11-01'),
(7, 4, 1, 2, 1, 'фламинго стали серыми', 'йцукенгшщз, фыв апмрол чсияор грашпасфр ыафшгыфырафггр ашарыаиылор щаршаышар фшащарышг пшфгрсгф', '2025-11-01'),
(9, 5, 1, 2, 2, 'новый айфон зомбирует', 'Осень — удивительное время года, когда природа готовится к зимнему покою. Листья на деревьях постепенно меняют цвет: от ярко‑зелёного к золотисто‑жёлтому, багряному и коричневому. Каждый день пейзаж выглядит иначе — словно художник наносит новые мазки на огромный холст.\r\n\r\nВ воздухе чувствуется особая свежесть, а утренние туманы придают окружающему миру загадочность. Птицы собираются в стаи и готовятся к перелёту в тёплые края. В парках и лесах становится тише: звери запасаются кормом и ищут убежища на зиму.\r\n\r\nЛюди тоже подстраиваются под смену сезона: достают тёплые вещи, пьют ароматный чай по вечерам и наслаждаются последними тёплыми днями. Осень вдохновляет на размышления, творчество и уютные домашние вечера. Это время сбора урожая, школьных занятий и подготовки к праздникам.\r\n\r\nНесмотря на прохладную погоду и частые дожди, осень обладает неповторимым очарованием. Она учит нас ценить каждый момент и находить красоту в простых вещах: в шуршании опавшей листвы, в каплях дождя на стекле, в мягком свете осеннего солнца', '2025-11-01'),
(17, 5, 3, 2, 2, 'амиция защищает своего брата', 'Осень — удивительное время года, когда природа готовится к зимнему покою. Листья на деревьях постепенно меняют цвет: от ярко‑зелёного к золотисто‑жёлтому, багряному и коричневому. Каждый день пейзаж выглядит иначе — словно художник наносит новые мазки на огромный холст.\r\n\r\nВ воздухе чувствуется особая свежесть, а утренние туманы придают окружающему миру загадочность. Птицы собираются в стаи и готовятся к перелёту в тёплые края. В парках и лесах становится тише: звери запасаются кормом и ищут убежища на зиму.\r\n\r\nЛюди тоже подстраиваются под смену сезона: достают тёплые вещи, пьют ароматный чай по вечерам и наслаждаются последними тёплыми днями. Осень вдохновляет на размышления, творчество и уютные домашние вечера. Это время сбора урожая, школьных занятий и подготовки к праздникам.\r\n\r\nНесмотря на прохладную погоду и частые дожди, осень обладает неповторимым очарованием. Она учит нас ценить каждый момент и находить красоту в простых вещах: в шуршании опавшей листвы, в каплях дождя на стекле, в мягком свете осеннего солнца', '2025-11-02'),
(18, 6, 4, 2, 3, 'тик ток рулит', 'Осень — удивительное время года, когда природа готовится к зимнему покою. Листья на деревьях постепенно меняют цвет: от ярко‑зелёного к золотисто‑жёлтому, багряному и коричневому. Каждый день пейзаж выглядит иначе — словно художник наносит новые мазки на огромный холст.\r\n\r\nВ воздухе чувствуется особая свежесть, а утренние туманы придают окружающему миру загадочность. Птицы собираются в стаи и готовятся к перелёту в тёплые края. В парках и лесах становится тише: звери запасаются кормом и ищут убежища на зиму.\r\n\r\nЛюди тоже подстраиваются под смену сезона: достают тёплые вещи, пьют ароматный чай по вечерам и наслаждаются последними тёплыми днями. Осень вдохновляет на размышления, творчество и уютные домашние вечера. Это время сбора урожая, школьных занятий и подготовки к праздникам.\r\n\r\nНесмотря на прохладную погоду и частые дожди, осень обладает неповторимым очарованием. Она учит нас ценить каждый момент и находить красоту в простых вещах: в шуршании опавшей листвы, в каплях дождя на стекле, в мягком свете осеннего солнца', '2025-11-02'),
(19, 5, 4, 2, 2, 'Зима', 'Зима — волшебное время, когда мир словно замирает в белоснежной сказке. С первыми морозами природа меняет свой облик: деревья покрываются инеем, реки сковывает лёд, а земля укутывается в пушистое одеяло снега. Каждый вдох наполняет лёгкие свежим, чуть колючим воздухом, а под ногами тихо хрустит нетронутая пелена.\r\n\r\nВ городах зима тоже преображается: улицы украшаются гирляндами и огнями, витрины магазинов пестрят праздничными декорациями, а в парках появляются ледяные скульптуры. Люди кутаются в тёплые шарфы, спешат домой с горячими напитками в руках и с нетерпением ждут новогодних чудес. Дети радуются первым снегопадам — лепят снеговиков, катаются на санках и играют в снежки, забыв о холоде.\r\n\r\nЗимний лес тих и величественен. Ели и сосны, припорошенные снегом, стоят словно стражи, а на сугробах видны следы зверей — зайцев, лисиц, лосей. В морозном воздухе изредка раздаётся стук дятла или треск ветки под тяжестью снега. Даже в самые лютые морозы природа не замирает полностью: птицы ищут корм, а некоторые звери продолжают свой путь сквозь заснеженные просторы.', '2025-11-02'),
(20, 4, 4, 2, 1, 'kiber-pank', 'кибер-панк это прям круто, остальное за меня скажет это:  рвиморвымиорвыиамотывомтолвытм врмпи раиршыпагнывп амгныпанып анмпывмп ывшмпышпмшгыврмшг ывпмшыв мывшмыимиышв памшыврашгырш ышарыгршы рвиморвымиорвыиамотывомтолвытм врмпи раиршыпагнывп амгныпанып анмпывмп ывшмпышпмшгыврмшг ывпмшыв мывшмыимиышв памшыврашгырш ышарыгршы рвиморвымиорвыиамотывомтолвытм врмпи раиршыпагнывп амгныпанып анмпывмп ывшмпышпмшгыврмшг ывпмшыв мывшмыимиышв памшыврашгырш ышарыгршы рвиморвымиорвыиамотывомтолвытм врмпи раиршыпагнывп амгныпанып анмпывмп ывшмпышпмшгыврмшг ывпмшыв мывшмыимиышв памшыврашгырш ышарыгршы рвиморвымиорвыиамотывомтолвытм врмпи раиршыпагнывп амгныпанып анмпывмп ывшмпышпмшгыврмшг ывпмшыв мывшмыимиышв памшыврашгырш ышарыгршы', '2025-11-11');

-- --------------------------------------------------------

--
-- Структура таблицы `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `role`
--

INSERT INTO `role` (`role_id`, `title`) VALUES
(1, 'Администратор'),
(2, 'Преподаватель'),
(3, 'Ученик');

-- --------------------------------------------------------

--
-- Структура таблицы `status`
--

CREATE TABLE `status` (
  `id_status` int(11) NOT NULL,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `status`
--

INSERT INTO `status` (`id_status`, `title`) VALUES
(1, 'на модерации'),
(2, 'опубликованно'),
(3, 'в архиве'),
(4, 'отклонено');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id_user`, `name`, `lastname`, `login`, `password`, `role_id`) VALUES
(4, 'Иван', 'Иванов', 'admin', 'admin123', 1),
(5, 'Мария', 'Петрова', 'teacher', 'teacher123', 2),
(6, 'Алексей', 'Сидоров', 'student', 'student123', 3),
(7, 'Анна', 'Смирнова', 'student2', 'password123', 3),
(8, 'Дмитрий', 'Козлов', 'student3', 'password123', 3),
(9, 'олег', 'назаров', 'oleg', 'oleg123', 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `id_news` (`id_news`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categories_id`);

--
-- Индексы таблицы `changing`
--
ALTER TABLE `changing`
  ADD PRIMARY KEY (`id_changing`),
  ADD KEY `id_news` (`id_news`),
  ADD KEY `id_user` (`id_user`);

--
-- Индексы таблицы `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id_nwes`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `categories_id` (`categories_id`),
  ADD KEY `id_status` (`id_status`);

--
-- Индексы таблицы `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Индексы таблицы `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id_status`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `archive`
--
ALTER TABLE `archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `categories_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `changing`
--
ALTER TABLE `changing`
  MODIFY `id_changing` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id_nwes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `status`
--
ALTER TABLE `status`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `archive`
--
ALTER TABLE `archive`
  ADD CONSTRAINT `archive_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `news` (`id_nwes`);

--
-- Ограничения внешнего ключа таблицы `changing`
--
ALTER TABLE `changing`
  ADD CONSTRAINT `changing_ibfk_1` FOREIGN KEY (`id_news`) REFERENCES `news` (`id_nwes`),
  ADD CONSTRAINT `changing_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Ограничения внешнего ключа таблицы `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Ограничения внешнего ключа таблицы `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`id_nwes`) REFERENCES `archive` (`id_news`),
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `news_ibfk_3` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`categories_id`),
  ADD CONSTRAINT `news_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
