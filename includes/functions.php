<?php
/* ═══════════════════════════════════════════════════════════════
   IUC Edu — Data & Functions
   ═══════════════════════════════════════════════════════════════ */

date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

// ── Site Config ───────────────────────────────────────────
define('SITE_NAME', 'IUC Edu');
define('SITE_TAGLINE', 'Launch Your Tech Career with Industry-Ready Skills');
define('SITE_URL', 'https://www.iucedu.com');
define('BASE_URL', rtrim((function() {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $siteRoot = dirname(__DIR__);
    $rel = str_replace($docRoot, '', str_replace('\\', '/', $siteRoot));
    return $rel ?: '';
})(), '/'));
define('SITE_PHONE', '7418048039');
define('SITE_EMAIL', 'info@iucedu.com');
define('SITE_ADDRESS', '#1&2 Gold Nest Apts, 2nd Main Road, C.I.T Nagar, Chennai – 600 035');
define('SITE_BRANCH', '19/11 Balakrishna Colony 1st St, Kaladipet, Thiruvottiyur, Chennai – 600 019');
define('WHATSAPP_NUMBER', '917418048039');
define('HEAD_OFFICE_LANDLINE', '04443539196');
define('BRANCH_LANDLINE', '04447940112');
define('FACEBOOK_URL', 'https://www.facebook.com/IUCComputerEducation');
define('INSTAGRAM_URL', 'https://www.instagram.com/iuc_computers/');
define('LINKEDIN_URL', 'https://www.linkedin.com/in/r-senthil-kumar-522213b9/');
define('YOUTUBE_URL', 'https://www.youtube.com/@iuccomputers5136');

// ── Navbar Links ──────────────────────────────────────────
$navLinks = [
    ['Home', 'home'],
    ['About', 'about'],
    ['Courses', 'courses'],
    ['Placements', 'placements'],
    ['Testimonials', 'testimonials'],
    ['Blog', 'blog'],
    ['Contact', 'contact'],
];

// ── Courses Data ──────────────────────────────────────────
$courses = [
    'ai-ml' => [
        'title' => 'Artificial Intelligence & Machine Learning',
        'short_title' => 'AI & ML',
        'slug' => 'ai-ml',
        'category' => 'ai',
        'description' => 'Master Artificial Intelligence, Machine Learning, Deep Learning, Generative AI, and Prompt Engineering. Build intelligent systems and deploy production-ready AI solutions.',
        'short_desc' => 'Master AI, ML, Deep Learning & Generative AI with hands-on projects.',
        'duration' => '6 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&q=80',
        'price' => '₹55,000',
        'emi' => '₹5,500/mo',
        'rating' => '4.9',
        'enrolled' => '1,200+',
        'badge' => 'AI Powered',
        'badge_color' => '#8B5CF6',
        'highlights' => [
            'Industry-recognized certification',
            '10+ real-world projects',
            'Generative AI & Prompt Engineering',
            'Placement assistance',
            'Lifetime access to materials',
            '1-on-1 mentorship',
        ],
        'technologies' => ['Python', 'TensorFlow', 'PyTorch', 'OpenAI', 'LangChain', 'Hugging Face', 'Streamlit', 'Docker'],
        'tools' => ['Jupyter', 'VS Code', 'Git', 'GitHub', 'AWS SageMaker', 'Google Colab', 'Weights & Biases'],
        'eligibility' => 'Any graduate with basic programming knowledge. Mathematics background is a plus but not mandatory.',
        'syllabus' => [
            ['Python for Data Science', 'Python basics, NumPy, Pandas, Matplotlib, Seaborn, Exploratory Data Analysis'],
            ['Statistics & Probability', 'Descriptive statistics, Inferential statistics, Probability distributions, Hypothesis testing'],
            ['Machine Learning Fundamentals', 'Supervised learning, Unsupervised learning, Regression, Classification, Clustering'],
            ['Advanced ML', 'Ensemble methods, Gradient Boosting, XGBoost, Feature engineering, Hyperparameter tuning'],
            ['Deep Learning', 'Neural networks, CNN, RNN, LSTM, Transfer learning, TensorFlow, Keras'],
            ['Natural Language Processing', 'Text preprocessing, Word embeddings, Transformers, BERT, GPT, LLMs'],
            ['Generative AI', 'Prompt Engineering, LangChain, RAG, Fine-tuning, AI Agents, ChatGPT API'],
            ['MLOps & Deployment', 'Docker, MLflow, CI/CD, Model serving, AWS SageMaker, Monitoring'],
            ['Capstone Project', 'End-to-end ML/AI project from data collection to deployment with real-world dataset'],
        ],
        'projects' => [
            'AI Chatbot using LangChain & OpenAI',
            'Face Recognition Attendance System',
            'Customer Sentiment Analysis',
            'Stock Price Prediction Model',
            'AI-Powered Recommendation Engine',
        ],
        'certification' => 'IUC Edu AI/ML Certification + Microsoft AI-900 Exam Preparation',
        'career' => [
            'AI/ML Engineer – ₹8L to ₹25L PA',
            'Data Scientist – ₹7L to ₹22L PA',
            'Deep Learning Engineer – ₹10L to ₹30L PA',
            'NLP Engineer – ₹9L to ₹24L PA',
            'Prompt Engineer – ₹8L to ₹20L PA',
            'AI Consultant – ₹12L to ₹35L PA',
        ],
    ],
    'data-science' => [
        'title' => 'Data Science & Analytics',
        'short_title' => 'Data Science',
        'slug' => 'data-science',
        'category' => 'data-science',
        'description' => 'Transform data into actionable insights. Learn Python, SQL, statistical analysis, data visualization, machine learning, and business intelligence tools.',
        'short_desc' => 'Transform data into insights with Python, SQL, ML & BI tools.',
        'duration' => '6 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&q=80',
        'price' => '₹55,000',
        'emi' => '₹5,500/mo',
        'rating' => '4.8',
        'enrolled' => '980+',
        'badge' => 'High Demand',
        'badge_color' => '#0EA5E9',
        'highlights' => [
            'Industry-recognized certification',
            '8+ real-world projects',
            'Power BI & Tableau training',
            'Placement assistance',
            'Lifetime access',
            'Expert mentorship',
        ],
        'technologies' => ['Python', 'SQL', 'Power BI', 'Tableau', 'Excel', 'Apache Spark', 'Hadoop', 'MongoDB'],
        'tools' => ['Jupyter', 'VS Code', 'Git', 'PostgreSQL', 'Snowflake', 'Airflow'],
        'eligibility' => 'Any graduate. No prior coding experience required.',
        'syllabus' => [
            ['Python Fundamentals', 'Python basics, data types, control flow, functions, OOP, file handling'],
            ['SQL & Databases', 'SQL queries, joins, subqueries, window functions, database design, PostgreSQL'],
            ['Data Wrangling', 'NumPy, Pandas, data cleaning, feature engineering, data transformation'],
            ['Data Visualization', 'Matplotlib, Seaborn, Plotly, Power BI dashboards, Tableau stories'],
            ['Statistics for Data Science', 'Descriptive statistics, probability, AB testing, regression analysis'],
            ['Machine Learning', 'Scikit-learn, regression, classification, clustering, model evaluation'],
            ['Big Data Basics', 'Hadoop, Spark, MapReduce, distributed computing concepts'],
            ['Capstone Project', 'End-to-end data science project with real-world dataset and business presentation'],
        ],
        'projects' => [
            'Sales Dashboard using Power BI',
            'Customer Segmentation Analysis',
            'E-Commerce Data Pipeline',
            'Real-time Analytics Dashboard',
            'Predictive Maintenance Model',
        ],
        'certification' => 'IUC Edu Data Science Certification + Microsoft PL-300 Exam Preparation',
        'career' => [
            'Data Scientist – ₹7L to ₹22L PA',
            'Data Analyst – ₹4L to ₹12L PA',
            'Business Intelligence Analyst – ₹5L to ₹15L PA',
            'Data Engineer – ₹8L to ₹20L PA',
            'Analytics Manager – ₹12L to ₹25L PA',
        ],
    ],
    'python' => [
        'title' => 'Python Programming',
        'short_title' => 'Python',
        'slug' => 'python',
        'category' => 'programming',
        'description' => 'Learn Python from scratch to advanced. Cover core programming, OOP, file handling, web scraping, APIs, and build real-world applications.',
        'short_desc' => 'Learn Python from basics to advanced with real-world projects.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner',
        'image' => 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=600&q=80',
        'price' => '₹35,000',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '1,500+',
        'badge' => 'Best Seller',
        'badge_color' => '#F59E0B',
        'highlights' => ['Beginner friendly', 'Certificate', '5 projects', 'Placement support', 'Lifetime access'],
        'technologies' => ['Python', 'Flask', 'Django', 'SQLite', 'PostgreSQL', 'BeautifulSoup', 'Selenium'],
        'tools' => ['VS Code', 'PyCharm', 'Git', 'GitHub', 'Postman'],
        'eligibility' => 'No prior programming experience required.',
        'syllabus' => [
            ['Python Basics', 'Installation, data types, variables, operators, string handling'],
            ['Control Flow & Functions', 'Loops, conditionals, functions, lambda, map, filter'],
            ['Data Structures', 'Lists, tuples, dictionaries, sets, comprehensions'],
            ['OOP in Python', 'Classes, objects, inheritance, polymorphism, encapsulation'],
            ['File Handling & Modules', 'File I/O, exception handling, modules, packages'],
            ['Web Scraping & APIs', 'BeautifulSoup, requests, REST APIs, JSON parsing'],
            ['Flask Web Framework', 'Routes, templates, forms, databases, deployment'],
            ['Capstone Project', 'Build and deploy a complete Python web application'],
        ],
        'projects' => [
            'Library Management System',
            'Weather App using API',
            'Web Scraper for Job Listings',
            'Blog Web Application with Flask',
            'Automated Email Sender',
        ],
        'certification' => 'IUC Edu Python Certification',
        'career' => ['Python Developer – ₹4L to ₹12L PA', 'Data Analyst – ₹4L to ₹10L PA', 'Automation Engineer – ₹5L to ₹14L PA'],
    ],
    'java' => [
        'title' => 'Java & J2EE Programming',
        'short_title' => 'Java',
        'slug' => 'java',
        'category' => 'programming',
        'description' => 'Master Core Java, J2EE, Spring Boot, Hibernate, and build enterprise-grade applications with industry best practices.',
        'short_desc' => 'Master Java, Spring Boot & J2EE for enterprise application development.',
        'duration' => '4 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&q=80',
        'price' => '₹46,000',
        'emi' => '₹2,500/mo',
        'rating' => '4.7',
        'enrolled' => '850+',
        'badge' => 'Popular',
        'badge_color' => '#0B5ED7',
        'highlights' => ['Industry certification', 'Live projects', 'Placement support', 'Expert mentorship'],
        'technologies' => ['Java', 'Spring Boot', 'Hibernate', 'JSP', 'Servlets', 'MySQL', 'Maven', 'JUnit'],
        'tools' => ['Eclipse', 'IntelliJ', 'Git', 'GitHub', 'Postman', 'Jenkins'],
        'eligibility' => 'Basic computer knowledge. No prior Java experience needed.',
        'syllabus' => [
            ['Core Java', 'Java basics, OOP, collections, multithreading, exception handling'],
            ['Advanced Java', 'JDBC, Servlets, JSP, JSTL, session management'],
            ['Spring Framework', 'Spring Core, IOC, DI, AOP, Spring MVC'],
            ['Spring Boot', 'Auto-configuration, REST APIs, Spring Data JPA, Security'],
            ['Hibernate', 'ORM, JPA, mappings, caching, criteria queries'],
            ['Database & SQL', 'MySQL, joins, transactions, indexing, stored procedures'],
            ['Build & Deploy', 'Maven, Jenkins, Docker, AWS deployment'],
            ['Capstone Project', 'Enterprise application with Spring Boot + Angular/React'],
        ],
        'projects' => [
            'Banking Application',
            'Hospital Management System',
            'E-Commerce Backend API',
            'Employee Payroll System',
            'Library Management with Spring Boot',
        ],
        'certification' => 'IUC Edu Java Certification + Oracle Java SE 8 Associate Preparation',
        'career' => ['Java Developer – ₹4L to ₹15L PA', 'Spring Boot Developer – ₹6L to ₹18L PA', 'Full Stack Developer – ₹6L to ₹20L PA'],
    ],
    'full-stack-java' => [
        'title' => 'Full Stack Java Development',
        'short_title' => 'Full Stack Java',
        'slug' => 'full-stack-java',
        'category' => 'full-stack',
        'description' => 'Become a complete Full Stack Java Developer. Master Core Java, Spring Boot, Hibernate, Angular/React, and cloud deployment.',
        'short_desc' => 'Build complete web apps with Java, Spring Boot & Angular/React.',
        'duration' => '7 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Pro',
        'image' => 'https://images.unsplash.com/photo-1547658719-da2b51169166?w=600&q=80',
        'price' => '₹66,000',
        'emi' => '₹5,500/mo',
        'rating' => '4.9',
        'enrolled' => '1,100+',
        'badge' => 'Most Popular',
        'badge_color' => '#F59E0B',
        'highlights' => ['Placement guarantee', '12+ projects', 'Industry certification', 'Mock interviews'],
        'technologies' => ['Java', 'Spring Boot', 'Angular', 'React', 'MySQL', 'MongoDB', 'Docker', 'AWS'],
        'tools' => ['IntelliJ', 'VS Code', 'Git', 'GitHub', 'Postman', 'Jenkins', 'Jira'],
        'eligibility' => 'Any graduate with basic computer knowledge.',
        'syllabus' => [
            ['Core Java', 'OOP, collections, streams, multithreading, exception handling'],
            ['Frontend with Angular', 'TypeScript, components, services, routing, forms, HTTP client'],
            ['Frontend with React', 'JSX, hooks, state management, Redux, React Router'],
            ['Spring Boot Backend', 'REST APIs, Spring Data JPA, Security, Microservices'],
            ['Database Management', 'MySQL, MongoDB, JPA, Hibernate, transactions'],
            ['Cloud & DevOps', 'Docker, AWS EC2/S3, CI/CD pipeline, deployment'],
            ['Capstone Project', 'Full stack enterprise application with all modern practices'],
        ],
        'projects' => [
            'E-Commerce Platform',
            'Hospital Management System',
            'CRM Application',
            'Banking Dashboard',
            'Learning Management System',
            'HRMS Portal',
        ],
        'certification' => 'IUC Edu Full Stack Java Certification + AWS Cloud Practitioner Preparation',
        'career' => ['Full Stack Developer – ₹6L to ₹22L PA', 'Java Developer – ₹5L to ₹15L PA', 'Software Engineer – ₹6L to ₹18L PA'],
    ],
    'spring-boot' => [
        'title' => 'Spring Boot & Microservices',
        'short_title' => 'Spring Boot',
        'slug' => 'spring-boot',
        'category' => 'backend',
        'description' => 'Deep dive into Spring Boot, REST APIs, Microservices architecture, Spring Cloud, and containerized deployment.',
        'short_desc' => 'Master Spring Boot, Microservices & Cloud-native development.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Intermediate',
        'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&q=80',
        'price' => '₹55,000',
        'emi' => '₹5,500/mo',
        'rating' => '4.7',
        'enrolled' => '620+',
        'badge' => 'Advanced',
        'badge_color' => '#8B5CF6',
        'highlights' => ['Advanced curriculum', 'Microservices focus', 'Cloud deployment', 'Industry projects'],
        'technologies' => ['Spring Boot', 'Spring Cloud', 'Spring Security', 'Docker', 'Kubernetes', 'Kafka', 'MySQL', 'MongoDB'],
        'tools' => ['IntelliJ', 'Postman', 'Git', 'Maven', 'Jenkins', 'SonarQube'],
        'eligibility' => 'Knowledge of Core Java required.',
        'syllabus' => [
            ['Spring Boot Advanced', 'Auto-configuration, Actuator, Profiles, logging, testing'],
            ['Building REST APIs', 'RESTful services, HATEOAS, versioning, Swagger/OpenAPI'],
            ['Microservices Architecture', 'Service discovery, API gateway, circuit breaker, distributed tracing'],
            ['Spring Cloud', 'Config server, Eureka, Zuul, Resilience4j, Sleuth'],
            ['Event-Driven Architecture', 'Kafka, RabbitMQ, event sourcing, CQRS'],
            ['Security', 'OAuth2, JWT, Spring Security, Keycloak'],
            ['Testing & Deployment', 'JUnit, Mockito, Testcontainers, Docker, K8s deployment'],
            ['Capstone Project', 'Build a complete microservices-based system'],
        ],
        'projects' => [
            'E-Commerce Microservices',
            'Banking API Gateway',
            'Order Management System',
            'Real-time Notification Service',
        ],
        'certification' => 'IUC Edu Spring Boot Certification',
        'career' => ['Backend Developer – ₹6L to ₹18L PA', 'Microservices Developer – ₹8L to ₹22L PA', 'Java Architect – ₹15L to ₹35L PA'],
    ],
    'react' => [
        'title' => 'React.js Frontend Development',
        'short_title' => 'React',
        'slug' => 'react',
        'category' => 'frontend',
        'description' => 'Build stunning, high-performance web applications with React.js, Redux, Next.js, and modern frontend tooling.',
        'short_desc' => 'Build modern web apps with React, Redux, Next.js & TypeScript.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&q=80',
        'price' => '₹35,000',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '900+',
        'badge' => 'Trending',
        'badge_color' => '#0EA5E9',
        'highlights' => ['Modern curriculum', 'TypeScript', 'Next.js', 'Real projects'],
        'technologies' => ['React', 'Redux', 'TypeScript', 'Next.js', 'Tailwind CSS', 'GraphQL', 'Jest'],
        'tools' => ['VS Code', 'Git', 'GitHub', 'Chrome DevTools', 'Vercel'],
        'eligibility' => 'Basic HTML, CSS, JavaScript knowledge required.',
        'syllabus' => [
            ['JavaScript ES6+', 'Variables, arrow functions, destructuring, modules, async/await'],
            ['React Fundamentals', 'JSX, components, props, state, lifecycle, hooks'],
            ['Advanced React', 'Context API, custom hooks, performance optimization, testing'],
            ['State Management', 'Redux, Redux Toolkit, Zustand, React Query'],
            ['TypeScript with React', 'Types, interfaces, generics, React with TypeScript'],
            ['Next.js Framework', 'SSR, SSG, API routes, file-based routing, deployment'],
            ['Styling & UI', 'Tailwind CSS, CSS modules, Material UI, responsive design'],
            ['Capstone Project', 'Build and deploy a complete React application'],
        ],
        'projects' => [
            'E-Commerce Frontend',
            'Dashboard with Charts',
            'Social Media Feed',
            'Real-time Chat Application',
            'Portfolio with Next.js',
        ],
        'certification' => 'IUC Edu React.js Certification',
        'career' => ['Frontend Developer – ₹4L to ₹14L PA', 'React Developer – ₹6L to ₹18L PA', 'UI Engineer – ₹7L to ₹20L PA'],
    ],
    'angular' => [
        'title' => 'Angular Frontend Development',
        'short_title' => 'Angular',
        'slug' => 'angular',
        'category' => 'frontend',
        'description' => 'Build enterprise-grade web applications with Angular, TypeScript, RxJS, NgRx, and modern Angular best practices.',
        'short_desc' => 'Build enterprise apps with Angular, TypeScript & RxJS.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1633356122102-3fe601e05bd2?w=600&q=80',
        'price' => '₹35,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.7',
        'enrolled' => '780+',
        'badge' => 'Popular',
        'badge_color' => '#EF4444',
        'highlights' => ['Enterprise focus', 'TypeScript', 'NgRx', 'Real projects'],
        'technologies' => ['Angular', 'TypeScript', 'RxJS', 'NgRx', 'Angular Material', 'Bootstrap'],
        'tools' => ['VS Code', 'Git', 'GitHub', 'Chrome DevTools', 'Firebase'],
        'eligibility' => 'Basic HTML, CSS, JavaScript knowledge required.',
        'syllabus' => [
            ['TypeScript', 'Types, classes, interfaces, decorators, generics'],
            ['Angular Fundamentals', 'Components, modules, templates, data binding, directives'],
            ['Services & Dependency Injection', 'Services, DI, HttpClient, observables, RxJS'],
            ['Routing & Navigation', 'Router, guards, resolvers, lazy loading'],
            ['Forms & Validation', 'Template-driven forms, reactive forms, custom validators'],
            ['State Management', 'NgRx, actions, reducers, effects, store'],
            ['Testing', 'Jasmine, Karma, unit testing, integration testing'],
            ['Capstone Project', 'Build and deploy a complete Angular application'],
        ],
        'projects' => [
            'Employee Management System',
            'Task Board Application',
            'E-Learning Platform',
            'Inventory Management Dashboard',
            'Hotel Booking System',
        ],
        'certification' => 'IUC Edu Angular Certification',
        'career' => ['Angular Developer – ₹5L to ₹16L PA', 'Frontend Lead – ₹8L to ₹22L PA', 'Full Stack Developer – ₹6L to ₹20L PA'],
    ],
    'node-js' => [
        'title' => 'Node.js Backend Development',
        'short_title' => 'Node.js',
        'slug' => 'node-js',
        'category' => 'backend',
        'description' => 'Build scalable server-side applications with Node.js, Express.js, MongoDB, and modern backend technologies.',
        'short_desc' => 'Build scalable backends with Node.js, Express & MongoDB.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Intermediate',
        'image' => 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=600&q=80',
        'price' => '₹37,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.7',
        'enrolled' => '720+',
        'badge' => 'Trending',
        'badge_color' => '#22C55E',
        'highlights' => ['Modern backend', 'REST & GraphQL', 'Real-time apps', 'Projects'],
        'technologies' => ['Node.js', 'Express.js', 'MongoDB', 'PostgreSQL', 'Socket.io', 'Redis', 'Docker'],
        'tools' => ['VS Code', 'Postman', 'Git', 'GitHub', 'MongoDB Compass'],
        'eligibility' => 'Basic JavaScript knowledge required.',
        'syllabus' => [
            ['Node.js Fundamentals', 'Event loop, modules, file system, streams, buffers'],
            ['Express.js', 'Routing, middleware, error handling, template engines'],
            ['Database with MongoDB', 'Mongoose, schemas, aggregation, indexing'],
            ['Database with PostgreSQL', 'SQL, Knex/Prisma, migrations, relations'],
            ['Authentication & Authorization', 'JWT, OAuth, sessions, bcrypt, security best practices'],
            ['Real-time with Socket.io', 'WebSockets, rooms, events, chat implementation'],
            ['Testing & Deployment', 'Jest, Supertest, CI/CD, Docker, cloud deployment'],
            ['Capstone Project', 'Build and deploy a complete Node.js backend'],
        ],
        'projects' => [
            'REST API for E-Commerce',
            'Real-time Chat Server',
            'Blog Platform Backend',
            'URL Shortener Service',
            'Task Management API',
        ],
        'certification' => 'IUC Edu Node.js Certification',
        'career' => ['Backend Developer – ₹5L to ₹16L PA', 'Node.js Developer – ₹6L to ₹18L PA', 'Full Stack Developer – ₹6L to ₹20L PA'],
    ],
    'ui-ux' => [
        'title' => 'UI/UX Design & Prototyping',
        'short_title' => 'UI/UX Design',
        'slug' => 'ui-ux',
        'category' => 'design',
        'description' => 'Become a professional UI/UX designer. Master Figma, user research, wireframing, prototyping, design systems, and portfolio creation.',
        'short_desc' => 'Become a pro designer with Figma, prototyping & design systems.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner',
        'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&q=80',
        'price' => '₹45,000',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '650+',
        'badge' => 'Creative',
        'badge_color' => '#EC4899',
        'highlights' => ['Figma mastery', 'Design systems', 'Portfolio', 'User research'],
        'technologies' => ['Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'After Effects'],
        'tools' => ['Figma', 'Miro', 'Notion', 'Zeplin', 'Prototyping tools'],
        'eligibility' => 'No prior design experience required.',
        'syllabus' => [
            ['Design Fundamentals', 'Color theory, typography, layout, visual hierarchy, Gestalt principles'],
            ['User Research', 'User interviews, surveys, personas, journey mapping, empathy maps'],
            ['Wireframing & Prototyping', 'Low-fidelity wireframes, high-fidelity mockups, interactive prototypes in Figma'],
            ['Visual Design', 'UI components, iconography, micro-interactions, accessibility, responsive design'],
            ['Design Systems', 'Component libraries, design tokens, style guides, Figma auto layout'],
            ['User Testing', 'Usability testing, A/B testing, heatmaps, analytics-driven design'],
            ['Portfolio Development', 'Case studies, presentation, personal brand, Dribbble/Behance'],
            ['Capstone Project', 'Complete product design from research to high-fidelity prototype'],
        ],
        'projects' => [
            'Mobile App Redesign',
            'E-Commerce Website Design',
            'SaaS Dashboard Design',
            'Design System Creation',
            'Portfolio Website',
        ],
        'certification' => 'IUC Edu UI/UX Design Certification + Adobe Certified Professional Preparation',
        'career' => ['UI/UX Designer – ₹4L to ₹14L PA', 'Product Designer – ₹7L to ₹22L PA', 'UX Researcher – ₹6L to ₹18L PA'],
    ],
    'software-testing' => [
        'title' => 'Software Testing & QA',
        'short_title' => 'Software Testing',
        'slug' => 'software-testing',
        'category' => 'testing',
        'description' => 'Master manual testing, automation testing with Selenium, API testing, performance testing, and QA best practices.',
        'short_desc' => 'Master manual & automation testing with Selenium & Cypress.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner',
        'image' => 'https://images.unsplash.com/photo-1516110833967-0b5716ca1387?w=600&q=80',
        'price' => '₹42,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.7',
        'enrolled' => '580+',
        'badge' => 'In Demand',
        'badge_color' => '#0B5ED7',
        'highlights' => ['Manual + Automation', 'Selenium', 'API testing', 'ISTQB prep'],
        'technologies' => ['Selenium', 'Cypress', 'JUnit', 'TestNG', 'Postman', 'JMeter', 'Jenkins'],
        'tools' => ['Eclipse', 'VS Code', 'Git', 'Jira', 'Bugzilla', 'Docker'],
        'eligibility' => 'Basic computer knowledge required.',
        'syllabus' => [
            ['Software Testing Fundamentals', 'SDLC, STLC, testing types, test case design, bug reporting'],
            ['Manual Testing', 'Test planning, test execution, defect tracking, test metrics'],
            ['Database Testing', 'SQL for testers, data validation, backend testing'],
            ['Selenium WebDriver', 'Java with Selenium, element locators, test frameworks, Page Object Model'],
            ['Advanced Automation', 'TestNG, Maven, data-driven testing, cross-browser testing'],
            ['API Testing', 'Postman, REST Assured, SOAP, API automation'],
            ['Performance Testing', 'JMeter, load testing, stress testing, performance tuning'],
            ['CI/CD & DevOps', 'Jenkins pipeline, Git integration, Docker for testing'],
        ],
        'projects' => [
            'E-Commerce Site Automation',
            'CRM Application Testing',
            'API Test Suite for Banking App',
            'Performance Test for Web App',
            'Mobile App Testing',
        ],
        'certification' => 'IUC Edu Software Testing Certification + ISTQB Foundation Preparation',
        'career' => ['QA Engineer – ₹3L to ₹10L PA', 'Automation Engineer – ₹5L to ₹15L PA', 'SDET – ₹6L to ₹18L PA'],
    ],
    'devops' => [
        'title' => 'DevOps & Cloud Engineering',
        'short_title' => 'DevOps',
        'slug' => 'devops',
        'category' => 'devops',
        'description' => 'Master DevOps culture, CI/CD pipelines, containerization, infrastructure as code, cloud services, and monitoring.',
        'short_desc' => 'Master CI/CD, Docker, Kubernetes, AWS & infrastructure automation.',
        'duration' => '4 Months',
        'mode' => 'Online / Offline',
        'level' => 'Intermediate',
        'image' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=600&q=80',
        'price' => '₹40,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '520+',
        'badge' => 'High Growth',
        'badge_color' => '#F59E0B',
        'highlights' => ['Docker & K8s', 'AWS/Azure', 'CI/CD', 'Real projects'],
        'technologies' => ['Docker', 'Kubernetes', 'Jenkins', 'Terraform', 'Ansible', 'AWS', 'Azure', 'Linux'],
        'tools' => ['Git', 'GitHub Actions', 'Helm', 'Prometheus', 'Grafana', 'ELK Stack'],
        'eligibility' => 'Basic Linux and programming knowledge required.',
        'syllabus' => [
            ['Linux Fundamentals', 'Commands, file system, permissions, shell scripting, networking'],
            ['Version Control & Git', 'Branching, merging, rebase, Git workflows, GitHub'],
            ['Containerization with Docker', 'Images, containers, Dockerfile, Compose, registry'],
            ['Orchestration with Kubernetes', 'Pods, services, deployments, config maps, Helm'],
            ['CI/CD Pipelines', 'Jenkins, GitHub Actions, GitLab CI, pipeline as code'],
            ['Infrastructure as Code', 'Terraform, Ansible, configuration management'],
            ['Cloud Platforms', 'AWS EC2, S3, Lambda, VPC, IAM; Azure basics'],
            ['Monitoring & Logging', 'Prometheus, Grafana, ELK, alerting, dashboards'],
        ],
        'projects' => [
            'CI/CD Pipeline for Java App',
            'Kubernetes Cluster Setup',
            'AWS Infrastructure with Terraform',
            'Dockerized Microservices',
            'Monitoring Stack with Prometheus & Grafana',
        ],
        'certification' => 'IUC Edu DevOps Certification + AWS Certified DevOps Engineer Preparation',
        'career' => ['DevOps Engineer – ₹6L to ₹22L PA', 'Cloud Engineer – ₹7L to ₹20L PA', 'Site Reliability Engineer – ₹10L to ₹30L PA'],
    ],
    'cloud-computing' => [
        'title' => 'Cloud Computing (AWS & Azure)',
        'short_title' => 'Cloud Computing',
        'slug' => 'cloud-computing',
        'category' => 'cloud',
        'description' => 'Architect, deploy, and manage enterprise cloud infrastructure on AWS and Azure. Prepare for global certifications.',
        'short_desc' => 'Architect cloud solutions with AWS & Azure. Dual certification track.',
        'duration' => '3 Months',
        'mode' => 'Online / Offline',
        'level' => 'Intermediate',
        'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=600&q=80',
        'price' => '₹46,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '760+',
        'badge' => 'Certified',
        'badge_color' => '#22C55E',
        'highlights' => ['Dual cloud', 'Certification prep', 'Hands-on labs', 'Real projects'],
        'technologies' => ['AWS EC2', 'AWS S3', 'Lambda', 'VPC', 'Azure VMs', 'Azure Functions', 'Terraform'],
        'tools' => ['AWS Console', 'Azure Portal', 'CLI', 'CloudFormation', 'Docker'],
        'eligibility' => 'Basic IT/infrastructure knowledge recommended.',
        'syllabus' => [
            ['Cloud Fundamentals', 'Cloud models, shared responsibility, pricing, global infrastructure'],
            ['AWS Compute', 'EC2, Auto Scaling, Load Balancers, Lambda, ECS, EKS'],
            ['AWS Storage & Database', 'S3, EBS, RDS, DynamoDB, ElastiCache'],
            ['AWS Networking', 'VPC, subnets, security groups, Route 53, CloudFront'],
            ['AWS Security & IAM', 'IAM policies, roles, KMS, WAF, Shield'],
            ['Azure Fundamentals', 'Azure VMs, App Services, Azure Functions, Storage'],
            ['Azure Networking & Security', 'VNet, NSG, Azure AD, Key Vault'],
            ['Infrastructure as Code', 'Terraform, CloudFormation, ARM templates'],
        ],
        'projects' => [
            'AWS 3-Tier Architecture',
            'Serverless Application on AWS',
            'Azure Migration Project',
            'Cloud Cost Optimization',
            'Disaster Recovery Setup',
        ],
        'certification' => 'AWS Certified Solutions Architect + Azure AZ-900 Preparation',
        'career' => ['Cloud Architect – ₹10L to ₹30L PA', 'Cloud Engineer – ₹6L to ₹20L PA', 'DevOps Engineer – ₹7L to ₹22L PA'],
    ],
    'cyber-security' => [
        'title' => 'Cyber Security & Ethical Hacking',
        'short_title' => 'Cyber Security',
        'slug' => 'cyber-security',
        'category' => 'security',
        'description' => 'Master penetration testing, network security, ethical hacking, and earn globally recognized certifications.',
        'short_desc' => 'Master ethical hacking, network security & penetration testing.',
        'duration' => '4 Months',
        'mode' => 'Online / Offline',
        'level' => 'Intermediate',
        'image' => 'https://images.unsplash.com/photo-1510511459019-5dda7724fd87?w=600&q=80',
        'price' => '₹45,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '890+',
        'badge' => 'High Demand',
        'badge_color' => '#EF4444',
        'highlights' => ['CEH prep', 'Live labs', 'Capture the Flag', 'Industry tools'],
        'technologies' => ['Kali Linux', 'Metasploit', 'Wireshark', 'Burp Suite', 'Nmap', 'Nessus'],
        'tools' => ['VirtualBox', 'VMware', 'Git', 'CTF platforms', 'Splunk'],
        'eligibility' => 'Basic networking and Linux knowledge recommended.',
        'syllabus' => [
            ['Networking Fundamentals', 'OSI model, TCP/IP, protocols, routing, subnetting'],
            ['Linux Security', 'Linux hardening, file permissions, user management, security tools'],
            ['Ethical Hacking', 'Reconnaissance, scanning, enumeration, exploitation, post-exploitation'],
            ['Network Security', 'Firewalls, IDS/IPS, VPN, network segmentation, monitoring'],
            ['Web Application Security', 'OWASP Top 10, SQL injection, XSS, CSRF, SSRF'],
            ['Penetration Testing', 'Metasploit, Burp Suite, Kali Linux, report writing'],
            ['Digital Forensics', 'Forensic tools, evidence collection, chain of custody'],
            ['Certification Prep', 'CEH exam prep, practice tests, lab exercises'],
        ],
        'projects' => [
            'Network Vulnerability Assessment',
            'Web App Penetration Test',
            'Malware Analysis Lab',
            'Security Operations Center Setup',
            'Incident Response Plan',
        ],
        'certification' => 'IUC Edu Cyber Security + CEH (EC-Council) Exam Preparation',
        'career' => ['Security Analyst – ₹5L to ₹16L PA', 'Penetration Tester – ₹7L to ₹22L PA', 'SOC Analyst – ₹4L to ₹12L PA', 'CISO – ₹20L to ₹50L PA'],
    ],
    'digital-marketing' => [
        'title' => 'Digital Marketing & SEO',
        'short_title' => 'Digital Marketing',
        'slug' => 'digital-marketing',
        'category' => 'marketing',
        'description' => 'Master SEO, Google Ads, Social Media Marketing, Email Marketing, Analytics, and growth hacking for modern businesses.',
        'short_desc' => 'Master SEO, Google Ads, Social Media & Growth Marketing.',
        'duration' => '2 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner',
        'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&q=80',
        'price' => '₹35,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '980+',
        'badge' => 'Fast Track',
        'badge_color' => '#06B6D4',
        'highlights' => ['Google certified', 'Live campaigns', 'Analytics', 'Placement support'],
        'technologies' => ['Google Ads', 'Meta Ads', 'Google Analytics', 'SEO tools', 'Mailchimp', 'Canva'],
        'tools' => ['SEMrush', 'Ahrefs', 'Google Search Console', 'Tag Manager', 'HubSpot'],
        'eligibility' => 'No prior marketing experience required. Basic computer skills needed.',
        'syllabus' => [
            ['Digital Marketing Fundamentals', 'Marketing landscape, customer journey, digital channels, strategy'],
            ['Search Engine Optimization', 'On-page SEO, off-page SEO, technical SEO, keyword research, content strategy'],
            ['Google Ads (PPC)', 'Search ads, display ads, remarketing, bidding strategies, quality score'],
            ['Social Media Marketing', 'Meta Ads, Instagram, LinkedIn, content calendar, community management'],
            ['Email Marketing', 'Campaign creation, automation, segmentation, A/B testing, Mailchimp'],
            ['Google Analytics', 'Setup, goals, events, reporting, conversion tracking, data analysis'],
            ['Content Marketing', 'Blog writing, video marketing, infographics, content distribution'],
            ['Growth Hacking', 'Virality, funnel optimization, experiment design, metrics & ROI'],
        ],
        'projects' => [
            'SEO Audit for Real Website',
            'Google Ads Campaign',
            'Social Media Content Calendar',
            'Email Marketing Funnel',
            'Complete Digital Strategy',
        ],
        'certification' => 'IUC Edu Digital Marketing + Google Digital Marketing Certification',
        'career' => ['Digital Marketer – ₹3L to ₹10L PA', 'SEO Specialist – ₹4L to ₹12L PA', 'PPC Analyst – ₹5L to ₹14L PA', 'Growth Marketer – ₹7L to ₹20L PA'],
    ],
    'c-cpp' => [
        'title' => 'C & C++ Programming',
        'short_title' => 'C & C++',
        'slug' => 'c-cpp',
        'category' => 'programming',
        'description' => 'Master C and C++ programming from fundamentals to advanced concepts including pointers, memory management, OOP, data structures, STL, and system-level programming. Build a strong foundation for competitive programming and software development.',
        'short_desc' => 'Master C & C++ programming, pointers, OOP, memory management & data structures.',
        'duration' => '4 Months',
        'mode' => 'Online / Offline',
        'level' => 'Beginner to Advanced',
        'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&q=80',
        'price' => '₹40,500',
        'emi' => '₹2,500/mo',
        'rating' => '4.8',
        'enrolled' => '1,500+',
        'badge' => 'Foundation',
        'badge_color' => '#059669',
        'highlights' => [
            'Strong programming foundation',
            '15+ hands-on projects',
            'Memory management mastery',
            'Placement assistance',
            'Lifetime access to materials',
            '1-on-1 mentorship',
        ],
        'technologies' => ['C', 'C++', 'GCC', 'GDB', 'STL', 'Data Structures', 'Algorithms', 'Make'],
        'tools' => ['VS Code', 'Code::Blocks', 'CLion', 'Git', 'Valgrind', 'GDB Debugger', 'Linux Terminal'],
        'eligibility' => 'Any student or professional wanting to build a strong programming foundation. No prior coding experience required.',
        'syllabus' => [
            ['Introduction to C', 'History of C, setting up environment, first program, compilation process, data types, variables, constants'],
            ['Operators & Expressions', 'Arithmetic, relational, logical, bitwise operators, type casting, operator precedence'],
            ['Control Flow', 'if-else, switch-case, for, while, do-while loops, break, continue, nested loops'],
            ['Functions in C', 'Function declaration, definition, call by value, call by reference, recursion, storage classes'],
            ['Arrays & Strings', 'One-dimensional and multi-dimensional arrays, string manipulation, string functions, array algorithms'],
            ['Pointers & Memory', 'Pointer basics, pointer arithmetic, pointers and arrays, dynamic memory allocation, malloc/calloc/free'],
            ['Structures & Unions', 'Structures, nested structures, arrays of structures, unions, typedef, enum, file handling'],
            ['C++ Fundamentals', 'C++ basics, references, function overloading, default arguments, iostream, namespaces'],
            ['OOP in C++', 'Classes and objects, constructors, destructors, inheritance, polymorphism, virtual functions'],
            ['Advanced C++', 'Templates, STL (vectors, maps, sets, algorithms), smart pointers, exception handling, file I/O'],
            ['Data Structures', 'Linked lists, stacks, queues, trees, graphs, sorting and searching algorithms in C/C++'],
            ['Project & Practice', 'System-level programming, competitive programming patterns, debugging with GDB, capstone project'],
        ],
        'projects' => [
            'Student Record Management System',
            'Library Management System using File I/O',
            'Singly & Doubly Linked List Implementation',
            'Student Database with Binary File Handling',
            'Custom Memory Allocator',
            'Mini STL – Vector, Map, Sort Implementation',
        ],
        'certification' => 'IUC Edu C & C++ Programming Certification',
        'career' => [
            'Systems Programmer – ₹5L to ₹15L PA',
            'Embedded Systems Engineer – ₹6L to ₹18L PA',
            'Software Developer (C/C++) – ₹5L to ₹16L PA',
            'Game Developer – ₹6L to ₹20L PA',
            'Competitive Programmer – ₹4L to ₹12L PA',
        ],
    ],
];

$courseCategories = [
    ['id' => 'ai', 'name' => 'Artificial Intelligence', 'icon' => 'bi bi-cpu'],
    ['id' => 'data-science', 'name' => 'Data Science', 'icon' => 'bi bi-bar-chart'],
    ['id' => 'programming', 'name' => 'Programming', 'icon' => 'bi bi-code-slash'],
    ['id' => 'full-stack', 'name' => 'Full Stack', 'icon' => 'bi bi-layers'],
    ['id' => 'frontend', 'name' => 'Frontend', 'icon' => 'bi bi-window'],
    ['id' => 'backend', 'name' => 'Backend', 'icon' => 'bi bi-server'],
    ['id' => 'design', 'name' => 'Design', 'icon' => 'bi bi-palette'],
    ['id' => 'testing', 'name' => 'Testing', 'icon' => 'bi bi-bug'],
    ['id' => 'devops', 'name' => 'DevOps', 'icon' => 'bi bi-gear'],
    ['id' => 'cloud', 'name' => 'Cloud', 'icon' => 'bi bi-cloud'],
    ['id' => 'security', 'name' => 'Security', 'icon' => 'bi bi-shield'],
    ['id' => 'marketing', 'name' => 'Marketing', 'icon' => 'bi bi-megaphone'],
];

// ── Stats Data ──────────────────────────────────────────
$stats = [
    ['value' => '25000', 'suffix' => '+', 'label' => 'Graduates Placed', 'icon' => 'bi bi-people'],
    ['value' => '50', 'suffix' => '+', 'label' => 'Courses Offered', 'icon' => 'bi bi-book'],
    ['value' => '98', 'suffix' => '%', 'label' => 'Placement Rate', 'icon' => 'bi bi-graph-up-arrow'],
    ['value' => '300', 'suffix' => '+', 'label' => 'Hiring Partners', 'icon' => 'bi bi-building'],
    ['value' => '18', 'suffix' => '+', 'label' => 'Years of Legacy', 'icon' => 'bi bi-award'],
    ['value' => '4.9', 'suffix' => '', 'label' => 'Student Rating', 'icon' => 'bi bi-star'],
];

// ── Why Choose Data ────────────────────────────────────
$whyChoose = [
    ['icon' => 'bi bi-person-badge', 'title' => 'Industry Expert Trainers', 'desc' => 'Learn from professionals with 10-20 years of experience from top tech companies.'],
    ['icon' => 'bi bi-laptop', 'title' => 'Live Projects', 'desc' => 'Build real-world applications with industry requirements, agile methodology, and code reviews.'],
    ['icon' => 'bi bi-briefcase', 'title' => 'Internship Program', 'desc' => 'Get hands-on industry experience with our 3-month internship program.'],
    ['icon' => 'bi bi-hand-thumbs-up', 'title' => 'Placement Assistance', 'desc' => 'Dedicated placement team with resume building, mock interviews, and job referrals.'],
    ['icon' => 'bi bi-chat-dots', 'title' => 'Mock Interviews', 'desc' => 'Technical, HR, and managerial mock interviews to build confidence and crack interviews.'],
    ['icon' => 'bi bi-file-earmark-text', 'title' => 'Resume Building', 'desc' => 'Professional resume and LinkedIn profile optimization by industry experts.'],
    ['icon' => 'bi bi-robot', 'title' => 'AI-Powered Tools', 'desc' => 'Access to cutting-edge AI tools for learning, coding, and project development.'],
    ['icon' => 'bi bi-calendar-check', 'title' => 'Flexible Batches', 'desc' => 'Morning, evening, weekend, and online batches to fit your schedule.'],
    ['icon' => 'bi bi-camera-reels', 'title' => 'Recorded Sessions', 'desc' => 'Every session recorded in HD and available on our learning portal 24/7.'],
    ['icon' => 'bi bi-patch-check', 'title' => 'Industry Certification', 'desc' => 'Earn globally recognized certifications from Microsoft, Google, AWS, and more.'],
];

// ── Technologies Data ──────────────────────────────────
$technologies = [
    ['name' => 'Java', 'icon' => 'bi bi-filetype-java'],
    ['name' => 'Python', 'icon' => 'bi bi-filetype-py'],
    ['name' => 'Spring Boot', 'icon' => 'bi bi-leaf'],
    ['name' => 'React', 'icon' => 'bi bi-react'],
    ['name' => 'Angular', 'icon' => 'bi bi-angular'],
    ['name' => 'Node.js', 'icon' => 'bi bi-node'],
    ['name' => 'MySQL', 'icon' => 'bi bi-database'],
    ['name' => 'MongoDB', 'icon' => 'bi bi-diagram-3'],
    ['name' => 'AWS', 'icon' => 'bi bi-cloud'],
    ['name' => 'Azure', 'icon' => 'bi bi-cloud'],
    ['name' => 'Docker', 'icon' => 'bi bi-box'],
    ['name' => 'Kubernetes', 'icon' => 'bi bi-grid'],
    ['name' => 'Git', 'icon' => 'bi bi-git'],
    ['name' => 'GitHub', 'icon' => 'bi bi-github'],
    ['name' => 'Postman', 'icon' => 'bi bi-envelope-paper'],
    ['name' => 'OpenAI', 'icon' => 'bi bi-cpu'],
    ['name' => 'LangChain', 'icon' => 'bi bi-link'],
    ['name' => 'TensorFlow', 'icon' => 'bi bi-graph-up'],
    ['name' => 'Power BI', 'icon' => 'bi bi-bar-chart'],
    ['name' => 'Tableau', 'icon' => 'bi bi-pie-chart'],
];

// ── Live Projects Data ────────────────────────────────
$liveProjects = [
    ['name' => 'Hospital Management', 'icon' => 'bi bi-hospital'],
    ['name' => 'Banking System', 'icon' => 'bi bi-bank'],
    ['name' => 'CRM Application', 'icon' => 'bi bi-people'],
    ['name' => 'ERP System', 'icon' => 'bi bi-gear-wide-connected'],
    ['name' => 'E-Commerce Platform', 'icon' => 'bi bi-cart'],
    ['name' => 'AI Chatbot', 'icon' => 'bi bi-robot'],
    ['name' => 'Face Recognition', 'icon' => 'bi bi-camera'],
    ['name' => 'Attendance System', 'icon' => 'bi bi-clipboard-check'],
    ['name' => 'Inventory Management', 'icon' => 'bi bi-boxes'],
    ['name' => 'Payroll System', 'icon' => 'bi bi-cash-stack'],
    ['name' => 'HRMS Portal', 'icon' => 'bi bi-person-badge'],
    ['name' => 'Healthcare App', 'icon' => 'bi bi-heart-pulse'],
];

// ── Project Detail Data (homepage Live Projects) ─────────────
$projectDetails = [
    'ecommerce' => [
        'id' => 'ecommerce',
        'title' => 'E-Commerce Platform',
        'tagline' => 'Full-stack online store with product catalog, cart, secure checkout and admin dashboard.',
        'overview' => 'A production-grade e-commerce application built end-to-end, mirroring modern retail platforms. It covers the complete buyer journey from browsing a rich product catalog to placing an order with secure payment, while the admin side manages inventory, orders and sales analytics from a single dashboard.',
        'image' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=900&q=80',
        'icon' => 'bi bi-cart',
        'gradient' => 'linear-gradient(135deg,#1D4ED8,#06B6D4)',
        'course' => 'full-stack-java',
        'course_name' => 'Full Stack Java Development',
        'domain' => 'Full Stack Web Development',
        'tools' => ['React', 'Spring Boot', 'MySQL', 'Stripe', 'Redis', 'JWT Auth', 'REST API'],
        'features' => [
            'Product catalog with search, filters and pagination',
            'Shopping cart with session & database persistence',
            'Secure checkout with Stripe payment gateway',
            'Order management with real-time status tracking',
            'Admin dashboard for inventory, orders and sales reports',
            'Role-based access control (customer / admin)',
            'RESTful API with JWT authentication & validation',
        ],
        'outcomes' => [
            'Deployable e-commerce platform with clean architecture',
            'Secure payment integration & transaction history',
            'Complete API documentation and database schema',
            'Versioned Git repository with clean commit history',
            'Live demo ready for portfolio & interviews',
        ],
    ],
    'hospital' => [
        'id' => 'hospital',
        'title' => 'Hospital Management System',
        'tagline' => 'Complete HMS covering registration, appointments, EMR, billing and pharmacy modules.',
        'overview' => 'A comprehensive hospital management system that digitizes core hospital operations. Patient records, appointments, electronic medical records (EMR), billing and pharmacy stock are unified in one secure platform, with role-specific dashboards for receptionists, doctors, nurses and administrators.',
        'image' => 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=900&q=80',
        'icon' => 'bi bi-hospital',
        'gradient' => 'linear-gradient(135deg,#7C3AED,#06B6D4)',
        'course' => 'node-js',
        'course_name' => 'Node.js & Angular',
        'domain' => 'Healthcare Software',
        'tools' => ['Angular', 'Node.js', 'Express', 'MongoDB', 'Docker', 'Mongoose', 'Socket.io'],
        'features' => [
            'Patient registration & centralized medical records',
            'Appointment scheduling with slot management',
            'Electronic Medical Records (EMR) module',
            'Billing, invoicing & insurance claims',
            'Pharmacy inventory & prescription tracking',
            'Role-based access for admin, doctor, nurse & reception',
            'Real-time notifications for appointments & alerts',
        ],
        'outcomes' => [
            'Working HMS with role-based dashboards',
            'Secure EMR storage with access controls',
            'Dockerised environment for easy deployment',
            'API documentation & data flow diagrams',
            'Interview-ready case study & walkthrough demo',
        ],
    ],
    'crm' => [
        'id' => 'crm',
        'title' => 'CRM Application',
        'tagline' => 'Sales CRM with lead tracking, pipeline management, analytics and email integration.',
        'overview' => 'A sales-focused CRM built for modern sales teams. It tracks leads through the entire pipeline, centralizes customer communication, automates follow-ups and turns raw activity data into actionable dashboards so teams can close deals faster.',
        'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=900&q=80',
        'icon' => 'bi bi-people',
        'gradient' => 'linear-gradient(135deg,#0EA5E9,#10B981)',
        'course' => 'python',
        'course_name' => 'Python Development',
        'domain' => 'Sales & Business Software',
        'tools' => ['Vue.js', 'Python', 'Django', 'PostgreSQL', 'Redis', 'Celery', 'SMTP Integration'],
        'features' => [
            'Lead capture & scoring across multiple sources',
            'Drag-and-drop pipeline management',
            'Customer 360° view with full interaction history',
            'Email integration & automated follow-up sequences',
            'Sales analytics dashboard with KPI reporting',
            'Activity timeline, tasks & reminders',
            'Exportable reports (CSV / PDF)',
        ],
        'outcomes' => [
            'Production-ready CRM with multi-tenant support',
            'Automated email workflows & notifications',
            'Real-time sales analytics & forecasting views',
            'Tested with automated unit & integration tests',
            'Portfolio demo with sample sales data',
        ],
    ],
    'ai-chatbot' => [
        'id' => 'ai-chatbot',
        'title' => 'AI Chatbot Assistant',
        'tagline' => 'GPT-powered assistant with RAG, context-aware answers and multi-language support.',
        'overview' => 'An intelligent customer-service chatbot powered by large language models. Using Retrieval-Augmented Generation (RAG), it answers questions from an organization\'s own knowledge base with sources cited, remembers conversation context, supports multiple languages and stays within safe guardrails.',
        'image' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=900&q=80',
        'icon' => 'bi bi-robot',
        'gradient' => 'linear-gradient(135deg,#F59E0B,#EF4444)',
        'course' => 'ai-ml',
        'course_name' => 'AI & Machine Learning',
        'domain' => 'Artificial Intelligence',
        'tools' => ['Python', 'LangChain', 'OpenAI API', 'FAISS', 'Streamlit', 'Docker', 'Hugging Face'],
        'features' => [
            'RAG pipeline with embeddings & vector search',
            'Context-aware, multi-turn conversations',
            'Multi-language support & tone control',
            'Knowledge-base ingestion with source citations',
            'Guardrails, prompt safety & content filtering',
            'Conversation analytics dashboard for admins',
        ],
        'outcomes' => [
            'Deployed chatbot with a custom knowledge base',
            'Documented RAG & retrieval pipeline',
            'Vector index management & fine-tuning notes',
            'API wrapper ready for website integration',
            'Live demo with industry use-case data',
        ],
    ],
    'analytics' => [
        'id' => 'analytics',
        'title' => 'Analytics Dashboard',
        'tagline' => 'Real-time business intelligence dashboard with interactive charts and predictive analytics.',
        'overview' => 'A real-time business intelligence dashboard that streams live data and renders interactive visualizations. Users can filter, drill down and export insights, while predictive models highlight trends and forecast key metrics — the kind of tool data teams use every day.',
        'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=900&q=80',
        'icon' => 'bi bi-graph-up-arrow',
        'gradient' => 'linear-gradient(135deg,#8B5CF6,#EC4899)',
        'course' => 'react',
        'course_name' => 'React Development',
        'domain' => 'Business Intelligence',
        'tools' => ['React', 'D3.js', 'Python', 'WebSocket', 'PostgreSQL', 'Chart.js', 'Node.js'],
        'features' => [
            'Real-time data streaming via WebSocket',
            'Interactive charts with drill-down & filters',
            'KPI cards, trend lines & comparison views',
            'Scheduled & on-demand report exports',
            'Predictive analytics with ML forecasting',
            'Role-based dashboards & data permissions',
        ],
        'outcomes' => [
            'Live BI dashboard with streaming data',
            'REST + WebSocket API layer for data delivery',
            'Forecast models with documented accuracy',
            'Reusable chart components library',
            'Cloud-deployed demo with sample datasets',
        ],
    ],
    'cyber-lab' => [
        'id' => 'cyber-lab',
        'title' => 'Cyber Security Lab',
        'tagline' => 'Sandboxed security lab for vulnerability scanning, penetration testing and threat monitoring.',
        'overview' => 'A controlled, sandboxed security lab where students practice ethical hacking safely. It includes vulnerability scanning, penetration-testing tooling, network traffic analysis and threat monitoring, producing professional reports — exactly the workflow of a real security analyst.',
        'image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=900&q=80',
        'icon' => 'bi bi-shield-check',
        'gradient' => 'linear-gradient(135deg,#06B6D4,#1D4ED8)',
        'course' => 'cyber-security',
        'course_name' => 'Cyber Security',
        'domain' => 'Information Security',
        'tools' => ['Kali Linux', 'Metasploit', 'Wireshark', 'Nmap', 'Burp Suite', 'Docker', 'Splunk'],
        'features' => [
            'Automated vulnerability scanning & assessment',
            'Penetration testing with exploit validation',
            'Network traffic capture & packet analysis',
            'Real-time threat monitoring & alerting',
            'Sandboxed lab environments via Docker',
            'Professional security report generation',
        ],
        'outcomes' => [
            'Complete penetration-testing report',
            'Vulnerability assessment documentation',
            'Packet-analysis case studies with Wireshark',
            'Repeatable lab setup for practice',
            'Portfolio of ethical-hacking case studies',
        ],
    ],
];

// ── Testimonials Data (Real IUC Website Reviews) ──────
$testimonials = [
    [
        'name' => 'Ramesh Subramanian',
        'role' => 'Full Stack Developer @ Infosys',
        'avatar' => 'assets/images/testimonials/ramesh.jpg',
        'rating' => 5,
        'quote' => 'IUC Computers completely changed my life. The hands-on projects and dedicated placement team secured my dream role at Infosys within 3 months. The mentors invest personally in every student\'s success.',
        'course' => 'Full Stack Development',
        'salary' => '₹8.5 LPA',
        'prev_salary' => '₹3.2 LPA',
        'batch' => '2023',
        'source' => 'google',
        'verified' => true,
    ],
    [
        'name' => 'Udhayakumar',
        'role' => 'Cybersecurity Analyst @ TCS',
        'avatar' => 'assets/images/testimonials/udhayakumar.jpg',
        'rating' => 5,
        'quote' => 'The Cybersecurity program was incredibly detailed — real labs, real attack scenarios. I passed my CEH exam on the first attempt. The institute truly prepares you for what the industry demands.',
        'course' => 'Cybersecurity',
        'salary' => '₹9.2 LPA',
        'prev_salary' => '₹4.1 LPA',
        'batch' => '2023',
        'source' => 'google',
        'verified' => true,
    ],
    [
        'name' => 'Sneha Meenakshi',
        'role' => 'Data Scientist @ Amazon',
        'avatar' => 'assets/images/testimonials/sneha.jpg',
        'rating' => 5,
        'quote' => 'From zero coding experience to landing a Data Science role at Amazon — IUC made it possible. The AI/ML curriculum is world-class and the career support team goes above and beyond.',
        'course' => 'Data Science & AI/ML',
        'salary' => '₹14 LPA',
        'prev_salary' => '₹5.5 LPA',
        'batch' => '2024',
        'source' => 'google',
        'verified' => true,
    ],
    [
        'name' => 'Vikram Nair',
        'role' => 'Cloud Architect @ Wipro',
        'avatar' => 'assets/images/testimonials/vikram.jpg',
        'rating' => 5,
        'quote' => 'The Cloud Computing bootcamp was intense in the best way possible. Got AWS certified and secured a Cloud Architect role within weeks of graduation. Best investment I ever made in my career.',
        'course' => 'Cloud Computing',
        'salary' => '₹12 LPA',
        'prev_salary' => '₹5 LPA',
        'batch' => '2024',
        'source' => 'google',
        'verified' => true,
    ],
    [
        'name' => 'Divya Sundararajan',
        'role' => 'UI/UX Lead @ Zoho',
        'avatar' => 'assets/images/testimonials/divya.jpg',
        'rating' => 5,
        'quote' => 'The UI/UX program taught me Figma, user research, and design systems from scratch. Now I lead a team of 6 designers at Zoho. IUC gave me the confidence and portfolio that got me hired.',
        'course' => 'UI/UX Design',
        'salary' => '₹10.5 LPA',
        'prev_salary' => '₹4.8 LPA',
        'batch' => '2023',
        'source' => 'google',
        'verified' => true,
    ],
    [
        'name' => 'Rahul Kumar',
        'role' => 'Digital Marketing Manager @ Swiggy',
        'avatar' => 'assets/images/testimonials/rahul.jpg',
        'rating' => 5,
        'quote' => 'In 2 months I learned Google Ads, Meta campaigns, and advanced SEO strategies. Got a 133% salary hike at Swiggy. The ROI on this course is absolutely phenomenal.',
        'course' => 'Digital Marketing',
        'salary' => '₹9.8 LPA',
        'prev_salary' => '₹4.2 LPA',
        'batch' => '2024',
        'source' => 'google',
        'verified' => true,
    ],
];

// ── Hiring Companies ──────────────────────────────────
$companies = [
    'TCS', 'Infosys', 'Wipro', 'HCL', 'Cognizant', 'Accenture',
    'Zoho', 'Freshworks', 'Capgemini', 'Tech Mahindra', 'IBM', 'Oracle',
    'Amazon', 'Microsoft', 'Google', 'Deloitte', 'Flipkart', 'PayPal',
];

// ── FAQ Data ──────────────────────────────────────────
$faqs = [
    ['q' => 'Which IT course is best for beginners?', 'a' => 'The right starting point depends on your goal. C and C++ build programming fundamentals, Python offers a beginner-friendly path into coding and data, while software testing, UI/UX design and digital marketing are options for learners exploring other technology roles.'],
    ['q' => 'Do I need programming experience to join?', 'a' => 'No prior programming experience is required for selected beginner courses. Every course page lists its eligibility and level so you can choose a program that matches your current skills.'],
    ['q' => 'Does IUC Edu provide programming and coding classes in Chennai?', 'a' => 'Yes. IUC Edu provides instructor-led programming classes in Chennai for C, C++, Python, Java, frontend, backend and full stack development, with practical exercises and projects.'],
    ['q' => 'What are the fees for computer courses?', 'a' => 'Fees vary by course, duration and learning track. Current fees and available payment options are shown on each course page; contact the admissions team to confirm the latest fee for your chosen batch.'],
    ['q' => 'Does IUC Edu provide placement assistance?', 'a' => 'Yes. Placement assistance includes resume preparation, LinkedIn guidance, mock technical and HR interviews, aptitude preparation and job referrals where suitable.'],
    ['q' => 'Are certificates provided after course completion?', 'a' => 'Yes. Students receive an IUC Edu course-completion certificate for the program they complete. Selected courses also include preparation for relevant external certification exams.'],
    ['q' => 'Can working professionals join the courses?', 'a' => 'Yes. Morning, evening and weekend batch options are designed to accommodate students and working professionals. Batch availability varies by course.'],
    ['q' => 'Are online computer and IT classes available?', 'a' => 'Yes. IUC Edu offers live instructor-led online classes as well as classroom training at its Chennai centres. Course pages identify the available training mode.'],
    ['q' => 'Where is IUC Edu located in Chennai?', 'a' => 'The head office is in C.I.T Nagar near Nandanam, Chennai, and another training centre is in Kaladipet, Thiruvottiyur. Maps and directions are available in the contact section.'],
    ['q' => 'How can I choose the right computer course?', 'a' => 'Compare the eligibility, curriculum, projects and career paths on each course page, or request counselling for guidance based on your experience and career goal.'],
];

// ── Blog Posts Data ──────────────────────────────────
$blogPosts = [
    [
        'slug' => 'top-programming-languages-2026',
        'title' => 'Top 10 Programming Languages to Learn in 2026',
        'excerpt' => 'Discover the most in-demand programming languages that will boost your career in 2026 and beyond.',
        'seo_title' => 'Top Programming Languages to Learn in 2026',
        'seo_description' => 'Discover the most in-demand programming languages to build your technology career in 2026, with practical learning guidance from IUC Edu in Chennai.',
        'content' => 'The technology landscape is evolving faster than ever. As we move through 2026, certain programming languages have emerged as must-learn skills for anyone looking to build a successful tech career. Here are the top 10 programming languages you should consider learning this year.

1. **Python** – Continues to dominate AI, ML, data science, and backend development. Its simplicity and vast ecosystem make it the #1 choice for beginners and experts alike.

2. **JavaScript/TypeScript** – The backbone of web development. With frameworks like React, Next.js, and Node.js, JavaScript remains indispensable. TypeScript adds type safety that enterprise projects demand.

3. **Java** – Still the king of enterprise applications. With Spring Boot and microservices architecture, Java powers millions of business-critical systems worldwide.

4. **Go (Golang)** – Google\'s language has gained massive traction in cloud-native development, microservices, and DevOps tooling due to its simplicity and performance.

5. **Rust** – Loved for its memory safety and performance. Growing rapidly in systems programming, WebAssembly, and blockchain development.

6. **Kotlin** – The modern alternative to Java for Android development and increasingly popular for backend development with Spring Boot.

7. **SQL** – Not a traditional programming language but essential for any data-related role. Every developer needs strong SQL skills.

8. **C#** – Strong in game development (Unity), enterprise applications, and Microsoft ecosystem development.

9. **Swift** – The language for iOS/macOS development. Growing with Apple\'s expanding ecosystem.

10. ** Dart/Flutter** – Google\'s UI toolkit for cross-platform mobile development. Increasingly popular for building beautiful native apps.

At IUC Edu, we offer comprehensive training in Python, Java, JavaScript, and many other languages. Our industry-aligned curriculum ensures you learn the skills that employers are actively hiring for.',
        'category' => 'Career Guidance',
        'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=600&q=80',
        'date' => 'Jan 15, 2026',
        'author' => 'Dr. Senthil Kumar R.',
    ],
    [
        'slug' => 'crack-data-science-interviews',
        'title' => 'How to Crack Data Science Interviews at Top Tech Companies',
        'excerpt' => 'Expert tips and strategies to ace data science interviews at FAANG and top product-based companies.',
        'seo_title' => 'Data Science Interview Preparation Guide',
        'seo_description' => 'Prepare for data science interviews with practical guidance on statistics, machine learning, SQL, Python, portfolio projects and behavioral questions.',
        'content' => 'Landing a data science role at a top tech company requires more than just technical knowledge. Here\'s our comprehensive guide to cracking data science interviews at FAANG and other top companies.

## 1. Master the Fundamentals
Ensure you have a solid grasp of:
- **Statistics & Probability**: Hypothesis testing, Bayesian thinking, distributions
- **Machine Learning**: Supervised and unsupervised learning, ensemble methods, feature engineering
- **SQL**: Complex queries, window functions, query optimization
- **Python**: Data manipulation with Pandas, NumPy, visualization

## 2. Practice Coding Problems
Data science interviews typically include coding rounds focused on:
- Algorithm implementation from scratch
- Data manipulation and cleaning
- Building and evaluating ML models
- Time-series analysis and forecasting

## 3. Understand the Business Context
Top companies expect you to:
- Connect technical solutions to business problems
- Design experiments and A/B tests
- Communicate findings to non-technical stakeholders
- Make data-driven recommendations

## 4. Build a Strong Portfolio
Showcase your skills through:
- End-to-end ML projects on GitHub
- Blog posts explaining your approach
- Kaggle competition participation
- Contributions to open-source projects

## 5. Prepare for Behavioral Questions
Use the STAR method (Situation, Task, Action, Result) to answer questions about:
- Past projects and challenges
- Team collaboration experiences
- How you handle failure and feedback
- Your motivation for pursuing data science

At IUC Edu, our Data Science program includes dedicated interview preparation, mock interviews, and direct referrals to our 300+ hiring partners.',
        'category' => 'Interview Tips',
        'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=80',
        'date' => 'Jan 10, 2026',
        'author' => 'Priya Anand',
    ],
    [
        'slug' => 'future-of-ai-generative-ai-2026',
        'title' => 'The Future of AI: Generative AI Trends in 2026',
        'excerpt' => 'Explore how Generative AI, LLMs, and Prompt Engineering are reshaping the technology landscape.',
        'seo_title' => 'Generative AI Trends to Watch in 2026',
        'seo_description' => 'Explore 2026 generative AI trends, including multimodal models, AI agents, enterprise adoption, governance and the technology skills employers need.',
        'content' => 'Generative AI has moved beyond novelty to become a core business tool. Here are the key trends shaping AI in 2026.

## 1. Multimodal AI Models
Modern AI models can now process and generate text, images, audio, and video simultaneously. This convergence is enabling:
- AI-powered content creation platforms
- Automated video production and editing
- Real-time translation with voice cloning
- Intelligent document processing

## 2. AI Agents & Automation
AI agents that can plan, reason, and execute complex tasks are becoming mainstream:
- Autonomous coding assistants
- AI-powered customer service agents
- Automated data pipeline management
- Self-optimizing marketing campaigns

## 3. Enterprise AI Adoption
Companies are moving from experimentation to production:
- Custom LLM fine-tuning for specific industries
- Retrieval-Augmented Generation (RAG) systems
- AI-powered business intelligence
- Automated compliance and reporting

## 4. Ethical AI & Governance
With great power comes great responsibility:
- AI safety and alignment research
- Bias detection and mitigation
- Regulatory compliance frameworks
- Transparent and explainable AI

## 5. Skills in Demand
The AI revolution is creating new career opportunities:
- Prompt Engineers
- AI Product Managers
- LLM Operations Engineers
- AI Ethics Specialists

IUC Edu\'s AI & ML program covers all these cutting-edge topics, preparing you for the future of technology.',
        'category' => 'AI Updates',
        'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=600&q=80',
        'date' => 'Jan 5, 2026',
        'author' => 'IUC Edu Team',
    ],
];

// ── Helper Functions ──────────────────────────────────

function getCourseBySlug($slug) {
    global $courses;
    return isset($courses[$slug]) ? $courses[$slug] : null;
}

function getCoursesByCategory($category) {
    global $courses;
    return array_filter($courses, function($c) use ($category) {
        return $c['category'] === $category;
    });
}

function getPopularCourses($limit = 6) {
    global $courses;
    return array_slice($courses, 0, $limit);
}

function formatPrice($price) {
    return $price;
}

function excerpt($text, $length = 120) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function renderBlogContent($text) {
    $lines = preg_split('/\R/', (string) $text);
    $html = '';
    $listOpen = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            if ($listOpen) {
                $html .= '</ul>';
                $listOpen = false;
            }
            continue;
        }

        $safe = htmlspecialchars($trimmed, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safe = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $safe);

        if (strpos($trimmed, '## ') === 0) {
            if ($listOpen) {
                $html .= '</ul>';
                $listOpen = false;
            }
            $heading = substr($safe, 3);
            $html .= '<h2>' . $heading . '</h2>';
            continue;
        }

        if (strpos($trimmed, '- ') === 0) {
            if (!$listOpen) {
                $html .= '<ul>';
                $listOpen = true;
            }
            $html .= '<li>' . substr($safe, 2) . '</li>';
            continue;
        }

        if ($listOpen) {
            $html .= '</ul>';
            $listOpen = false;
        }
        $html .= '<p>' . $safe . '</p>';
    }

    if ($listOpen) {
        $html .= '</ul>';
    }

    return $html;
}

function socialIcon($platform) {
    $icons = [
        'facebook' => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
        'instagram' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z',
        'linkedin' => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zM4 6a2 2 0 100-4 2 2 0 000 4z',
        'youtube' => 'M22.54 6.42a2.78 2.78 0 00-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.4 19.54C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 001.94-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z',
    ];
    return $icons[strtolower($platform)] ?? '';
}
