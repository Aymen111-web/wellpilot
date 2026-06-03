# WellPilot

## AI-Powered Wellness & Resort Experience Platform

### Wellness Hackathon 2026 Project Documentation

---

# 1. Executive Summary

WellPilot is a web-based platform that combines Artificial Intelligence, wellness tracking, and hospitality experiences to help users improve their physical and mental well-being.

The platform analyzes wellness data such as stress levels, sleep quality, hydration, and activity levels, then provides personalized wellness recommendations and suitable resort-based experiences.

By integrating AI-powered wellness coaching with wellness tourism and hospitality services, WellPilot bridges the gap between digital wellness monitoring and real-world wellness experiences.

---

# 2. Problem Statement

Many students, professionals, and travelers experience:

* Stress and burnout
* Poor sleep habits
* Lack of wellness awareness
* Difficulty finding suitable wellness activities

Although wellness resorts and wellness centers provide relaxation services, users often struggle to identify which wellness experiences are most beneficial for their personal condition.

There is currently no unified platform that combines:

* Wellness assessment
* AI-based wellness guidance
* Hospitality and resort recommendations

---

# 3. Proposed Solution

WellPilot provides a complete wellness ecosystem that:

1. Evaluates a user's wellness condition.
2. Generates a wellness score.
3. Uses AI to provide personalized recommendations.
4. Recommends wellness activities and resort experiences.
5. Encourages healthy habits through challenges and progress tracking.

---

# 4. Objectives

## Primary Objectives

* Improve wellness awareness.
* Reduce stress and burnout.
* Promote healthy lifestyle habits.
* Deliver personalized wellness guidance.
* Connect users with wellness experiences.

## Secondary Objectives

* Support wellness tourism.
* Promote hospitality innovation.
* Demonstrate practical AI implementation.
* Create a scalable wellness platform.

---

# 5. Target Users

## Students

* Stress management
* Study-life balance
* Productivity improvement

## Professionals

* Burnout prevention
* Work-life balance
* Mental wellness

## Travelers

* Wellness tourism
* Relaxation planning

## Resort Guests

* Personalized wellness recommendations
* Wellness activity planning

---

# 6. Technology Stack

## Frontend

* Vue.js 3
* Vite
* Tailwind CSS
* Axios

Folder Structure:

frontend/

## Backend

* Laravel
* REST API Architecture

Folder Structure:

backend/

## Database

* MySQL

Reason:

* Easy Laravel integration
* Compatible with cPanel hosting
* Reliable and simple deployment

## AI Services

* Gemini API

Used For:

* Wellness analysis
* Personalized recommendations
* AI wellness coaching

---

# 7. System Architecture

Vue.js Frontend

↓

Laravel REST API

↓

MySQL Database

↓

Gemini AI API

---

# 8. Core Features

## Feature 1: Wellness Assessment

Users complete a wellness questionnaire.

Assessment Areas:

* Stress level
* Sleep quality
* Water intake
* Physical activity
* Mood status

Output:

* Wellness score
* Wellness category
* Personalized suggestions

Example:

Wellness Score: 85/100

Status: Good

Recommendation:
Maintain current wellness habits.

---

## Feature 2: AI Wellness Coach

Users can interact with an AI assistant.

Example Input:

"I feel stressed and exhausted after studying for many hours."

Example Output:

"You may be experiencing mental fatigue. Consider resting, staying hydrated, and taking short breaks throughout your study sessions."

Capabilities:

* Wellness advice
* Stress management tips
* Sleep recommendations
* Productivity guidance

---

## Feature 3: Wellness Dashboard

Displays:

* Wellness score
* Stress indicators
* Sleep analytics
* Hydration tracking
* Activity tracking

Dashboard Components:

* Progress cards
* Statistics
* Charts
* Trend analysis

---

## Feature 4: Resort Wellness Recommendation Engine

The platform recommends wellness activities based on assessment results.

Examples:

High Stress:

* Spa therapy
* Meditation
* Nature walks
* Relaxation sessions

Low Physical Activity:

* Swimming
* Fitness programs
* Yoga sessions

Poor Sleep Quality:

* Sleep wellness programs
* Relaxation retreats

---

## Feature 5: Wellness Challenges

Users participate in wellness challenges.

Examples:

* 7-Day Hydration Challenge
* Better Sleep Challenge
* Daily Walking Challenge
* Stress-Free Week Challenge

Benefits:

* Motivation
* Consistency
* Engagement

---

# 9. Database Design

## wellness_assessments

Fields:

* id
* nickname
* stress_level
* sleep_hours
* water_intake
* activity_level
* mood_level
* wellness_score
* created_at

---

## ai_conversations

Fields:

* id
* question
* response
* created_at

---

## wellness_challenges

Fields:

* id
* challenge_name
* description
* duration_days
* reward_points
* created_at

---

## resort_recommendations

Fields:

* id
* wellness_category
* activity_name
* description
* created_at

---

# 10. Main Pages

## Home Page

Purpose:

* Introduce the platform
* Explain benefits
* Start wellness assessment

Sections:

* Hero section
* Features overview
* Call to action

---

## Wellness Assessment Page

Purpose:

* Collect wellness information
* Generate wellness score

---

## Dashboard Page

Purpose:

* Display analytics
* Show recommendations

---

## AI Coach Page

Purpose:

* AI chat interface and voice chat
* Wellness guidance

---

## Resort Recommendations Page

Purpose:

* Show recommended wellness activities
* Explain wellness benefits

---

## Challenges Page

Purpose:

* Display wellness challenges
* Track progress

---

# 11. Innovation

WellPilot combines three important sectors:

1. Artificial Intelligence
2. Wellness Technology
3. Hospitality & Wellness Tourism

Innovation Formula:

AI Wellness Coach

*

Wellness Analytics

*

Resort Experience Recommendations

=

Personalized Wellness Ecosystem

---

# 12. Alignment with Hackathon Theme

Theme:

Heal. Build. Thrive.

How WellPilot Aligns:

Heal:

* Wellness guidance
* Stress reduction

Build:

* AI-powered technology solution

Thrive:

* Healthy lifestyle development
* Wellness engagement

---

# 13. Future Expansion

Phase 2:

* Resort booking system
* Appointment scheduling
* Event registration

Phase 3:

* Wearable device integration
* Smartwatch support
* Health monitoring

Phase 4:

* Mobile application
* Multi-resort partnerships
* Wellness marketplace

---

# 14. Project Folder Structure

project-root/

├── backend/

│ ├── app/

│ ├── routes/

│ ├── database/

│ ├── resources/

│ └── .env

│

├── frontend/

│ ├── src/

│ │ ├── components/

│ │ ├── pages/

│ │ ├── services/

│ │ ├── router/

│ │ └── assets/

│

│ ├── package.json

│ └── vite.config.js

│

└── README.md

---

# 15. Minimum Viable Product (MVP)

Features to Complete First:

1. Wellness Assessment
2. AI Wellness Coach
3. Wellness Dashboard
4. Resort Recommendation Engine

These four modules provide a complete demonstration of the platform's value and are sufficient for a strong hackathon presentation.
 
 
 # AI Editor Rules

## 1. Stay On Task
- Only respond to the user’s request.
- Do not add extra features, opinions, or unrelated info.

## 2. Be Concise
- Prefer short, clear answers.
- Avoid repetition and filler text.

## 3. No Assumptions
- If something is unclear, ask a question.
- Do not guess missing requirements.

## 4. Code Safety
- Do not introduce unsafe, destructive, or destructive commands.
- Avoid deleting or modifying unrelated code.

## 5. Format Discipline
- Follow requested formats exactly (JSON, code, markdown, etc.).
- Do not wrap outputs in extra commentary unless asked.

## 6. Honesty
- If you don’t know, say so.
- Do not fabricate results or citations.

## 7. No Overreach
- Don’t expand scope beyond the prompt.
- Don’t “improve” unless explicitly requested.

make it mobile responsive and support language toggle to amharic and english
Language Rule:

Always communicate in the same language used by the user, whether the user communicates through text or voice.

* If the user speaks or writes in Amharic, respond in Amharic.
* If the user speaks or writes in English, respond in English.
* If the user uses mixed languages, respond in the user's primary language.
* The language used for voice responses must match the language used for text responses.
* Never switch languages unless the user switches languages first.
You are WellPilot AI Coach, a wellness assistant.

Rules:

1. Always reply in the same language as the user's message.

   * If the user writes in Amharic, reply in Amharic.
   * If the user writes in English, reply in English.
   * If the user mixes languages, reply using the user's primary language.

2. Keep responses short, practical, and focused.

   * Maximum 2-4 sentences unless the user asks for more details.
   * Do not give long explanations.

3. Answer only what the user asks.

   * Do not add unrelated information.
   * Do not lecture or provide lengthy background information.

4. Provide wellness-focused suggestions only.

   * Focus on sleep, hydration, exercise, relaxation, stress management, healthy habits, skincare, and general wellness.
   * Do not diagnose diseases.
   * Do not claim to be a doctor.

5. If the user reports a wellness concern, provide practical actions.

Examples:

User: "I am tired."
Response: "You may need rest. Try drinking water, taking a short break, and getting enough sleep tonight."

User: "ደክሞኛል።"
Response: "ትንሽ እረፍት ያድርጉ፣ ውሃ ይጠጡ እና በቂ እንቅልፍ ይውሰዱ።"

User: "My face is dark."
Response: "Try staying hydrated, getting enough sleep, and following a regular skincare routine. Gentle facial steaming may also help."

User: "ፊቴ ጠቁሯል።"
Response: "በቂ ውሃ ይጠጡ፣ በቂ እንቅልፍ ይተኙ እና መደበኛ የቆዳ እንክብካቤ ያድርጉ። ቀላል የፊት እንፋሎት መውሰድም ሊረዳ ይችላል።"

6. If the user greets you, respond briefly and politely.

7. Never generate harmful, unsafe, or illegal advice.

8. Maintain a supportive, professional, and friendly tone.
Amharic Communication Rules:

* When responding in Amharic, use natural, fluent, and modern Ethiopian Amharic.
* Respond as a native Amharic speaker would in everyday conversation.
* Avoid literal word-for-word translations from English.
* Use clear, simple, and culturally appropriate Amharic.
* Prefer common Ethiopian expressions over formal or machine-translated language.
* Keep responses concise, helpful, and easy to understand.
* Maintain the same wellness-focused tone in both Amharic and English.
* If the user speaks casually, respond casually and naturally.
* If the user speaks formally, respond formally and respectfully.

Examples:

User: "ደክሞኛል"

Response:
"ትንሽ እረፍት ያድርጉ፣ ውሃ ይጠጡ እና ዛሬ በቂ እንቅልፍ ለማግኘት ይሞክሩ።"

User: "ፊቴ ጠቁሯል"

Response:
"በቂ ውሃ ይጠጡ፣ በቂ እንቅልፍ ይተኙ እና መደበኛ የቆዳ እንክብካቤ ያድርጉ። ቀላል የፊት እንፋሎት መውሰድም ሊረዳ ይችላል።"

Response Rules:

* Do not introduce yourself unless the user explicitly asks who you are.
* start responses with phrases such as:

  * "Welcome to WellPilot AI Coach"
  * "Hello, I am WellPilot AI Coach"
  * "Thank you for using WellPilot"
* Answer the user's question directly.
* Focus only on the user's request.
* Keep responses concise and practical.
* Do not repeat greetings after the first interaction.
* Do not include unnecessary introductions, conclusions, or promotional text.
* Provide wellness-focused suggestions immediately.

Examples:

User: "I am tired."

Good Response:
"Try drinking water, taking a short break, and getting enough sleep tonight."

Bad Response:
"Welcome to WellPilot AI Coach. I am here to help you. Since you are tired..."

User: "ደክሞኛል።"

Good Response:
"ትንሽ እረፍት ያድርጉ፣ ውሃ ይጠጡ እና በቂ እንቅልፍ ይውሰዱ።"

Bad Response:
"ወደ WellPilot AI Coach እንኳን ደህና መጡ..."

The Ai should be General Ai assistant , it can answer  about anything.
and it should be able to access the internet to get the latest information.
and it should be able to access the local files to get the local information.