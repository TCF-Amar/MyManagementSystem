import pymysql
from faker import Faker
import random
import hashlib

# Create a Faker instance
fake = Faker()

# Database connection
conn = pymysql.connect(
    host='localhost',      # Replace with your database host
    user='root',  # Replace with your database username
    password='',  # Replace with your database password
    db='mydatabase',    # Replace with your database name
)

try:
    with conn.cursor() as cursor:
        for _ in range(100):
            first_name = fake.first_name()
            last_name = fake.last_name()
            gender = random.choice(['Male', 'Female', 'Others'])
            role = random.randint(1, 3)  # Assuming you have 5 roles in the empRole table
            address = fake.address()
            contact_number = fake.phone_number().replace('-', '').replace('(', '').replace(')', '').replace(' ', '')
            email = fake.email()
            password = "1234"
            hashed_password = hashlib.sha256(password.encode()).hexdigest()

            sql = f"""
                INSERT INTO employee (firstName, lastName, gender, role, address, contact, email, password)
                VALUES ('{first_name}', '{last_name}', '{gender}', {role}, '{address}', '{contact_number}', '{email}', '{hashed_password}')
            """
            cursor.execute(sql)
        
        conn.commit()
        print("100 fake records inserted successfully.")

except Exception as e:
    print(f"An error occurred: {e}")

finally:
    conn.close()
