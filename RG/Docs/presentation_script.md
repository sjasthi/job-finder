# RoleGenie Presentation Script

## Presentation Order
1. Introduction
2. Problem the project solves
3. What the project does
4. How it works
5. Technology and architecture
6. Purpose and value
7. Closing and future improvements

---

## Script

### 1. Introduction
"Good afternoon, my name is [Your Name], and today I will be presenting RoleGenie, our capstone project. RoleGenie is a web-based job search and application support platform designed to help job seekers find opportunities and prepare stronger applications with the help of AI."

### 2. Problem the project solves
"Job searching can be overwhelming. Many people spend hours searching through different websites, updating resumes, and writing cover letters for each job. This process is time-consuming and often frustrating, especially when applicants are trying to tailor their materials to each opportunity."

### 3. What the project does
"RoleGenie solves this problem by bringing the process into one place. Users can create an account, upload a resume, search for jobs, and generate customized resume content and cover letters that are more relevant to the role they are applying for. The system also stores job-related information in a personal dashboard so users can keep track of their applications."

### 4. How it works
"The workflow is simple. First, the user signs up and logs in. Then they upload their resume, which is stored and parsed so the system can understand their experience. After that, the user searches for jobs using the application. The system pulls job listings from external job sources and presents them in a user-friendly interface. For each job, the user can generate a tailored resume version and a cover letter using AI."

### 5. Technology and architecture
"The project is built using PHP, MySQL, Bootstrap, and API integrations. PHP is the main server-side language that powers the web pages and handles user login, resume uploads, and job searches. MySQL stores user accounts, resume data, and job results so the system can keep track of information over time. Bootstrap is used to make the interface clean and responsive, so the app is easy to use on different devices. The project also uses Composer, which is a dependency manager for PHP. Composer helps install and organize third-party libraries that the app needs, such as PDF parsing tools and other supporting packages. One important library is smalot/pdfparser, which is used to read PDF resumes and extract the text from them so the system can understand the user’s experience. Another important technology is JSearch, which is a job search API that pulls listings from popular job platforms like LinkedIn, Indeed, Glassdoor, and Monster. This allows the application to bring job data into one place instead of forcing the user to search across multiple websites manually. The app also connects to the Claude AI API, which helps generate customized resumes and cover letters based on the user’s profile and the job description. Together, these technologies make the system more intelligent, more automated, and more useful for job seekers."

### 6. Purpose and value
"The purpose of RoleGenie is to make job applications easier, faster, and more personalized. Instead of manually rewriting documents for every new job, users can rely on the platform to help them create better materials and stay organized. In short, the project aims to reduce the stress of job hunting and improve the quality of applications."

### 7. Closing and future improvements
"In conclusion, RoleGenie is a smart and practical tool that combines job discovery, resume support, and application organization into one platform. While the current version focuses on core features, future improvements could include more automation, stronger security features, and deeper AI integration. Thank you."

---

## Shorter Version for a 1-Minute Talk
"RoleGenie is a web application created to help job seekers manage the application process more efficiently. Users can sign up, upload a resume, search for jobs, and use AI to generate tailored cover letters and resume content for specific roles. The platform is built with PHP, MySQL, and API integrations, and it helps users stay organized through a dashboard. The main purpose of the project is to save time, improve application quality, and make job searching less stressful."
