import mysql.connector
from mysql.connector import Error
from faker import Faker
import random

# Initialize Faker library to generate fake data
fake = Faker()

# Connect to the MySQL database
try:
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="mydatabase"
    )

    if db.is_connected():
        print("Connected to the database")

    cursor = db.cursor()

    # Fetch existing course IDs
    cursor.execute("SELECT id FROM courses")
    course_ids = [course[0] for course in cursor.fetchall()]

    # Ensure there are course IDs available
    if not course_ids:
        raise ValueError("No course IDs found in the courses table")

    # SQL query to insert data
    insert_query = """
    INSERT INTO students (
        rollNo, enrollNo, firstName, lastName, fatherName, contact, email, 
        gender, dob, address, courseId, courseYear
    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    # Function to generate a random student record
    def generate_student_data():
        roll_no = fake.unique.random_number(digits=8)
        enroll_no = fake.unique.random_number(digits=8)
        first_name = fake.first_name()
        last_name = fake.last_name()
        father_name = fake.first_name()
        contact = fake.unique.random_number(digits=10)
        email = fake.unique.email()
        gender = random.choice(['Male', 'Female', 'Others'])
        dob = fake.date_of_birth(tzinfo=None, minimum_age=18, maximum_age=25)
        address = fake.address()
        course_id = random.choice(course_ids)
        course_year = random.randint(1, 4)  # Assuming a 4-year course structure
        
        # Hash rollNo to use as password and convert it to a hexadecimal string
        
        return (
            str(roll_no), str(enroll_no), first_name, last_name, father_name, str(contact), 
            email, gender, dob, address, course_id, course_year
        )

    # Insert 1000 rows of data
    for _ in range(900):
        student_data = generate_student_data()
        cursor.execute(insert_query, student_data)

    # Commit the transaction
    db.commit()

    print("1000 rows of data have been inserted into the students table.")

except Error as e:
    print(f"Error: {e}")
    if db.is_connected():
        db.rollback()

finally:
    if db.is_connected():
        cursor.close()
        db.close()
        print("Database connection closed.")
