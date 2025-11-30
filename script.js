let currentUser = null;


let users = [
    {
        name: "admin",
        password: "admin123",
        id: "x000000",
        email: "unitrack@admin.com",
        role: "admin"
    },
    {
        name: "Almustafa Alamri",
        password: "password123",
        id: "s151920",
        email: "s151920@student.squ.edu.om",
        role: "student",
        record: [
            {
                courseCode: "COMP3700",
                grade: "A"
            },
            {
                courseCode: "MATH1010",
                grade: "B+"
            },
            {
                courseCode: "COMP2202",
                grade: "A-"
            }
        ]
    },
    {
        name: "Awab Alshukairi",
        password: "mypassword",
        id: "s142364",
        email: "s142364@student.squ.edu.om",
        role: "student",
        record: [
            {
                courseCode: "COMP3700",
                grade: "A"
            },
            {
                courseCode: "MATH1010",
                grade: "B+"
            },
            {
                courseCode: "COMP3501",
                grade: "C-"
            }
        ]
    },
    {
        name: "Ahmed Soleimani",
        password: "teachpass",
        id: "i121212",
        email: "a.soleimani@squ.edu.om",
        role: "teacher"
    }
]

let courses = [
    {
        code: "COMP3700",
        title: "Web Development",
        instructor: "Ahmed Soleimani",
        credits: 3,
        seats: 30
    },
    {
        code: "MATH1010",
        title: "Calculus I",
        instructor: "Dr. Sebti Kerbal",
        credits: 4,
        seats: 25
    },
    {
        code: "COMP3501",
        title: "Computer organization and Assembly language",
        instructor: "Dr. Amjad Altobi", 
        credits: 3,
        seats: 20
    },
    {
        code: "COMP2202",
        title: "Introduction to object oriented programming",
        instructor: "Dr. Donald Trump", 
        credits: 3,
        seats: 35
    }

]

const getGradePoints = (grade) => {
    const gradePointsMap = {
        "A": 4.0,
        "A-": 3.7,
        "B+": 3.3,
        "B": 3.0,
        "B-": 2.7,
        "C+": 2.3,
        "C": 2.0,
        "C-": 1.7,
        "D+": 1.3,
        "D": 1.0,
        "F": 0.0
    };
    return gradePointsMap[grade] || 0.0;
}


const updateCourseTable = () => {
    const courseTableBody = document.getElementById("course-table-body");
    if (!courseTableBody) return;

    courses.forEach(course => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${course.code}</td>
            <td>${course.title}</td>
            <td>${course.instructor}</td>
            <td>${course.credits}</td>
            <td>${course.seats}</td>
        `;
        courseTableBody.appendChild(row);
    });
}


const updateResultsTable = () => {
    const resultsTableBody = document.getElementById("results-table-body");
    const student = users.find(user => user.role === "student" && user.id === "s151920"); // Example: get student with id s151920

    if (!student) return;
    if (!resultsTableBody) return;
    student.record.forEach(record => {
        const course = courses.find(c => c.code === record.courseCode);
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${course.code}</td>
            <td>${course.title}</td>
            <td>${course.credits}</td>
            <td>${record.grade}</td>
            <td>${getGradePoints(record.grade)}</td>
        `;
        resultsTableBody.appendChild(row);
    });
}


document.addEventListener('DOMContentLoaded', function() {
    updateCourseTable();
    updateResultsTable();
});
