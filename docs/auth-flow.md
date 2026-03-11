← [Documentation](README.md) | [Architecture](architecture.md) | [Design Principles](design-principles.md)
---

## Registration Flow

### Step 1 — HTTP Request

**Component:** `RegisterController`  
**Responsibility:**  
Receives the registration request and validates the incoming data.

---

### Step 2 — Registration Service

**Component:** `RegisterUserService`  
**Responsibility:**  
Handles the registration workflow and coordinates the process.

Main tasks:

- create the user entity
- hash the password
- assign default roles
- persist the user

---

### Step 3 — Pre-Registration Event

**Component:** `BeforeUserRegisterEvent`  
**Responsibility:**  
Allows additional actions to run before the user is fully registered.

---

### Step 4 — Token Issuing

**Component:** `TokenRequestService`  
**Responsibility:**  
Generates and stores the email confirmation token.

Main tasks:

- generate raw token
- hash token
- persist token request
- associate token with user

---

### Step 5 — Token Event

**Component:** `ConfirmationTokenIssuedEvent`  
**Responsibility:**  
Signals that a confirmation token has been created.

---

### Step 6 — Email Request

**Component:** `ConfirmationEmailRequestedEvent`  
**Responsibility:**  
Requests the sending of the confirmation email.

---

### Step 7 — Event Subscriber

**Component:** `AuthSubscriber`  
**Responsibility:**  
Listens to authentication events and prepares technical actions such as email sending.

---

### Step 8 — Email Generation

**Component:** `MailBuilder`  
**Responsibility:**  
Generates the confirmation email and prepares the message content.

---

### Step 9 — Email Delivery

**Component:** Mail transport system  
**Responsibility:**  
Sends the confirmation email to the user.