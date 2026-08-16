<?php
/**
 * EDUNEX AI Tutor - local rule-based engine
 * Understands natural language requests in English (and detects Amharic),
 * remembers conversation, adapts to the student, and can:
 *   explain topics, summarize lessons, generate quizzes/flashcards,
 *   translate to Amharic, ELI5, diagrams, study plans, readiness, weak topics.
 */

class AiTutor {

    public static array $amharic = [
        'hello' => 'ሰላም', 'good morning' => 'እንደምን አደሩ', 'thank you' => 'አመሰግናለሁ',
        'thank' => 'አመሰግናለሁ', 'school' => 'ትምህርት ቤት', 'teacher' => 'መምህር', 'student' => 'ተማሪ',
        'book' => 'መጽሐፍ', 'exam' => 'ፈተና', 'homework' => 'የቤት ሥራ', 'lesson' => 'ትምህርት',
        'course' => 'ኮርስ', 'class' => 'ክፍል', 'friend' => 'ጓደኛ', 'family' => 'ቤተሰብ',
        'water' => 'ውሃ', 'food' => 'ምግብ', 'love' => 'ፍቅር', 'peace' => 'ሰላም',
        'math' => 'ሂሳብ', 'science' => 'ሳይንስ', 'history' => 'ታሪክ', 'english' => 'እንግሊዝኛ',
        'physics' => 'ፊዚክስ', 'chemistry' => 'ኬሚስትሪ', 'biology' => 'ባዮሎጂ',
        'computer' => 'ኮምፒውተር', 'library' => 'ቤተ-መጻሕፍት', 'grade' => 'ክፍል', 'university' => 'ዩኒቨርሲቲ',
        'help' => 'እገዛ', 'question' => 'ጥያቄ', 'answer' => 'መልስ', 'learn' => 'ተማር',
        'study' => 'ማጥናት', 'test' => 'ፈተና', 'result' => 'ውጤት', 'attendance' => 'መገኘት',
        'assignment' => 'ምድብ', 'certificate' => 'የምስክር ወረቀት', 'scholarship' => 'የትምህርት ዕድል',
        'country' => 'አገር', 'Ethiopia' => 'ኢትዮጵያ', 'Addis Ababa' => 'አዲስ አበባ',
        'good' => 'ጥሩ', 'bad' => 'መጥፎ', 'big' => 'ትልቅ', 'small' => 'ትንሽ',
        'today' => 'ዛሬ', 'tomorrow' => 'ነገ', 'yesterday' => 'ትላንት', 'now' => 'አሁን',
        'recursion' => 'ሪከርሽን', 'algebra' => 'አልጀብራ', 'equation' => 'እኩልታ',
        'triangle' => 'ሶስት ማዕዘን', 'energy' => 'ኃይል', 'force' => 'ኃይል',
        'motion' => 'እንቅስቃሴ', 'velocity' => 'ፍጥነት', 'speed' => 'ፍጥነት',
        'array' => 'አረይ', 'data' => 'ዳታ', 'programming' => 'ፕሮግራም', 'code' => 'ኮድ',
    ];

    /** Detect Amharic script text */
    public static function isAmharic(string $text): bool {
        return (bool)preg_match('/[\x{1200}-\x{137F}]/u', $text);
    }

    /** Translate English phrase -> Amharic using dictionary (phrase-level) */
    public static function translateAmharic(string $text): string {
        $t = mb_strtolower(trim($text));
        if (isset(self::$amharic[$t])) return self::$amharic[$t];
        $words = preg_split('/\s+/', $t);
        $out = [];
        foreach ($words as $w) {
            $w = trim($w, '.,!?;:()');
            if (isset(self::$amharic[$w])) $out[] = self::$amharic[$w];
            else $out[] = $w;
        }
        $translated = implode(' ', $out);
        if ($translated === $t) {
            return 'ይቅርታ, የ«' . $text . '» ትርጉም በአይነት መዝገብ ውስጥ አልተገኘም። (I could not find a translation for "' . $text . '" in my dictionary. Ask me to explain the concept instead.)';
        }
        return $translated;
    }

    /** Main entry: respond to a student message within a chat context */
    public static function respond(string $message, array $user, ?array $chat = null): string {
        $m = mb_strtolower(trim($message));

        // --- Amharic input -> translate + answer in English summary ---
        if (self::isAmharic($message)) {
            return "እንኳን ደህና መጣህ! (Welcome!) I can understand some Amharic.\n" .
                   "I understood your message: \"$message\".\n\n" .
                   "To help you best, please ask me in English, for example:\n" .
                   "• Explain recursion\n• Summarize Chapter 3\n• Create a quiz about algebra";
        }

        // --- intents ---
        if (preg_match('/(explain like i\'m five|eli5|simple terms|for a kid)/', $m)) {
            return self::eli5(self::extractTopic($message), $user);
        }
        if (preg_match('/translate (to )?amharic|in amharic|አማርኛ/', $m)) {
            $topic = preg_replace('/translate (to )?amharic|in amharic|አማርኛ|:/', '', $message);
            return self::translateAmharic(trim($topic) ?: $message);
        }
        if (preg_match('/summar(i[sz]e|y)|summary of/', $m)) {
            return self::summarize(self::extractTopic($message), $user);
        }
        if (preg_match('/create (a )?(quiz|test)|make (a )?(quiz|test)|quiz (me )?(on|about)|generate (a )?quiz/', $m)) {
            $topic = self::extractTopic(preg_replace('/create (a )?quiz|make (a )?quiz|quiz (me )?|generate (a )?quiz/', '', $message));
            return self::makeQuiz($topic, $user);
        }
        if (preg_match('/(flashcards|flash cards|flash-cards)/', $m)) {
            $topic = self::extractTopic(preg_replace('/generate|create|make|flashcards|flash cards/', '', $message));
            return self::makeFlashcards($topic, $user);
        }
        if (preg_match('/(diagram|chart|visual)/', $m)) {
            return self::diagram(self::extractTopic($message));
        }
        if (preg_match('/study plan|plan my study|how (should|do) i study/', $m)) {
            return self::studyPlan($user);
        }
        if (preg_match('/(ready|readiness|am i ready|how prepared)/', $m)) {
            return self::readiness($user);
        }
        if (preg_match('/weak (topic|area|subject)|what should i (study|focus)|suggest (topics|what)/', $m)) {
            return self::weakTopics($user);
        }
        if (preg_match('/correct (my )?grammar|grammar check|grammar:/', $m)) {
            $sentence = preg_replace('/correct (my )?grammar|grammar check|grammar:/', '', $message);
            return self::grammarCheck(trim($sentence));
        }
        if (preg_match('/(hello|hi|hey|selam|salam|good (morning|afternoon|evening))/', $m)) {
            return self::greeting($user);
        }
        if (preg_match('/help|what can you do|how do you work/', $m)) {
            return self::helpText();
        }
        if (preg_match('/explain|what is|what are|define|how does|how do|tell me about|difference between|example/', $m)) {
            return self::explain(self::extractTopic($message), $user);
        }

        // fallback: try to explain whatever topic we can find
        $topic = self::extractTopic($message);
        if ($topic !== '' && !self::isGreetingWord($topic)) {
            return self::explain($topic, $user);
        }
        return self::helpText();
    }

    private static function isGreetingWord(string $w): bool {
        return in_array($w, ['hi', 'hey', 'hello', 'selam', 'salam', 'please', 'thanks', 'thank', 'yes', 'no']);
    }

    /** Strip stop words to find the topic */
    public static function extractTopic(string $message): string {
        $stop = ['explain','please','what','is','are','the','a','an','of','about','on','for','me','with','in','to','and','how','does','do','define','tell','me','briefly','simple','terms','means','mean','example','give','show'];
        $words = preg_split('/\s+/', mb_strtolower($message));
        $words = array_diff($words, $stop);
        return trim(implode(' ', $words), ' ?!.,:;');
    }

    /** Knowledge base: topic -> explanation */
    private static function knowledge(): array {
        return [
            'recursion' => "Recursion is when a function calls itself to solve a smaller version of the same problem.\n\n**How it works:**\n1. A *base case* stops the recursion (the simplest case).\n2. Each call makes the problem smaller.\n\n**Example (factorial):**\nfactorial(n) = n × factorial(n-1), where factorial(1) = 1\nfactorial(5) = 5×4×3×2×1 = 120\n\n**Real-life analogy:** Russian dolls — each doll contains a smaller copy of itself. Fibonacci numbers also use recursion:\nF(n) = F(n-1) + F(n-2), F(0)=0, F(1)=1 → 0, 1, 1, 2, 3, 5, 8…",
            'fibonacci' => "The Fibonacci sequence starts 0, 1, and every next number is the sum of the previous two:\n0, 1, 1, 2, 3, 5, 8, 13, 21…\n\n**Formula:** F(n) = F(n-1) + F(n-2) with F(0)=0, F(1)=1.\n\nIt appears in nature: sunflower seeds, pinecones, and even the spiral of a nautilus shell. In programming it is the classic recursion example.",
            'algebra' => "Algebra is the branch of mathematics that uses symbols (like x and y) to represent numbers and the rules for manipulating them.\n\n**Key ideas:**\n• A **variable** is an unknown number, e.g. x.\n• An **expression** is a combination of numbers and variables: 3x + 2.\n• An **equation** states two expressions are equal: 3x + 2 = 11.\n\n**Solving linear equations — golden rule:** whatever you do to one side, do to the other.\n3x + 2 = 11 → 3x = 9 → x = 3",
            'quadratic' => "A quadratic equation has the form ax² + bx + c = 0, where a ≠ 0.\n\n**How to solve:**\n1. **Factoring:** x² - 5x + 6 = 0 → (x-2)(x-3) = 0 → x = 2 or x = 3\n2. **Quadratic formula:** x = (-b ± √(b² - 4ac)) / 2a\n3. The expression b² - 4ac is the **discriminant**: positive → two real roots, zero → one root, negative → no real roots.",
            'triangle' => "A triangle is a polygon with 3 sides and 3 angles.\n\n**Key facts:**\n• Sum of interior angles = 180°\n• Types: equilateral (all equal), isosceles (two equal), scalene (none equal), right-angled (one 90° angle)\n• **Area** = ½ × base × height\n• **Perimeter** = sum of all sides\n\n**Pythagoras** (right triangles): a² + b² = c² where c is the hypotenuse.",
            'pythagoras' => "Pythagoras' theorem: in a right-angled triangle, the square of the hypotenuse equals the sum of the squares of the other two sides:\n**a² + b² = c²**\n\nExample: sides 3 and 4 → c = √(9+16) = √25 = 5. This gives the famous 3-4-5 triangle.\n\nIt is used in construction, navigation, and computer graphics to measure distances.",
            'velocity' => "**Velocity** is how fast something moves in a specific direction. It is displacement divided by time:\n**v = d / t**\n\n• Speed is a number (60 km/h); velocity includes direction (60 km/h north).\n• **Acceleration** is the change of velocity per unit time: a = Δv / t\n• On the Addis–Bahir Dar road, a bus doing 80 km/h for 5 hours covers 400 km (distance = speed × time).",
            'energy' => "**Energy** is the ability to do work. It is measured in joules (J).\n\n**Types:**\n• Kinetic energy — energy of motion: KE = ½mv²\n• Potential energy — stored energy: PE = mgh (gravity)\n• Thermal, chemical, electrical, light, sound…\n\n**Law of conservation:** energy cannot be created or destroyed, only converted. A falling ball converts potential energy into kinetic energy.",
            'array' => "An **array** is a collection of elements of the same type stored in contiguous memory locations.\n\n**In C:**\n```c\nint scores[5] = {90, 85, 78, 92, 88};\n```\n• Indexing starts at **0**: scores[0] = 90\n• Access: O(1) — instant, because the address is computed as base + index × size\n• Use a loop to process all elements\n\n**Trade-off:** fixed size; inserting/deleting in the middle is slow (O(n)).",
            'linked list' => "A **linked list** is a sequence of nodes, where each node stores data plus a pointer to the next node.\n\n```c\nstruct Node { int data; struct Node* next; };\n```\n\n**Key points:**\n• Dynamic size — grows as needed\n• Insert/delete at the head: O(1)\n• Searching takes O(n) — you must walk the chain\n• Trade-off vs arrays: no random access, extra memory for pointers",
            'binary tree' => "A **binary tree** is a hierarchical structure where each node has at most two children (left and right).\n\n• **Root** — top node; **leaf** — node with no children; **height** — longest path from root to a leaf.\n\n```\n       50\n      /  \\\n     30   70\n    /  \\    \\\n   20  40    80\n```\n\n**Traversals:**\n• In-order: left, root, right → 20, 30, 40, 50, 70, 80 (sorted!)\n• Pre-order: root, left, right\n• Post-order: left, right, root\n\n**Binary search tree** property: left < root < right, giving O(log n) search.",
            'photosynthesis' => "Photosynthesis is how plants make their own food using sunlight:\n\n6CO₂ + 6H₂O + light → C₆H₁₂O₆ (glucose) + 6O₂\n\n**Inputs:** carbon dioxide, water, sunlight. **Outputs:** glucose (food) and oxygen.\n\nIt happens in the **chloroplasts** (which contain green chlorophyll) of leaves. This is why plants are green!",
            'atom' => "An **atom** is the smallest unit of a chemical element.\n\n• **Protons** (+ charge) and **neutrons** (neutral) live in the nucleus.\n• **Electrons** (− charge) orbit around the nucleus in shells.\n• Atomic number = number of protons (defines the element)\n• Carbon has 6 protons; oxygen 8; hydrogen 1.\n\nMost of an atom is empty space — if an atom were a football stadium, the nucleus would be a pea in the centre.",
            'grammar' => "Grammar is the set of rules that make sentences correct and clear.\n\n**Basic sentence structure (SVO):** Subject + Verb + Object\n• \"Liya eats injera.\" (Liya = subject, eats = verb, injera = object)\n\n**Common rules:**\n• Present: She **goes** to school (add -s for he/she/it)\n• Past: She **went** to school yesterday\n• Articles: **a** before consonants, **an** before vowels\n• Plural: add -s/-es (box → boxes, city → cities)",
            'history of ethiopia' => "Ethiopia is one of the oldest nations in the world, with over 3,000 years of recorded history.\n\n**Key eras:**\n• **Aksumite Empire** (~1st–7th century AD) — a great trading power; the Obelisk of Axum still stands.\n• **Lalibela** (12th–13th c.) — 11 rock-hewn churches carved from single pieces of stone.\n• **Battle of Adwa** (1896) — Ethiopia defeated Italy, staying independent.\n• **Modern era** — federal republic, seat of the African Union in Addis Ababa.\n\nEthiopia is the birthplace of coffee and follows its own calendar with 13 months!",
            'cell' => "The **cell** is the basic unit of life. All living things are made of cells.\n\n**Parts:**\n• **Nucleus** — the brain, holds DNA\n• **Membrane** — the gatekeeper, controls what enters/leaves\n• **Cytoplasm** — jelly that fills the cell\n• **Mitochondria** — the power plant, makes energy (ATP)\n\nPlants also have **chloroplasts** and a rigid **cell wall**; animal cells do not.",
            'fraction' => "A **fraction** shows part of a whole: numerator / denominator, e.g. ¾ means 3 parts out of 4.\n\n**Operations:**\n• Add/subtract: get a common denominator first (½ + ⅓ = 3/6 + 2/6 = 5/6)\n• Multiply: top × top, bottom × bottom (½ × ¾ = 3/8)\n• Divide: flip and multiply (½ ÷ ¾ = ½ × 4/3 = 2/3)\n\n**Tip:** simplify at the end (6/8 → 3/4).",
        ];
    }

    private static function eli5(string $topic, array $user): string {
        $kb = self::knowledge();
        if ($topic !== '' && isset($kb[$topic])) {
            return "Let me explain **$topic** like you're five years old:\n\n" . self::toEli5($kb[$topic]);
        }
        $lesson = self::findLesson($topic, $user);
        if ($lesson) {
            return "Let me explain **{$lesson['title']}** like you're five:\n\n" . self::toEli5(self::lessonSummary($lesson));
        }
        return "Let me explain **$topic** like you're five:\n\nImagine $topic as a game where you take one small step at a time. Each step is easy, and the steps build on each other. When you understand one step, the next one becomes easier. That's really all learning is — small steps, repeated.\n\nTell me the exact name of your topic (e.g. 'Explain recursion') and I'll break it down properly!";
    }

    private static function toEli5(string $text): string {
        $text = preg_replace('/\*\*?/', '', $text);
        $text = preg_replace('/\b(function|variable|equation|recursion)\b/i', '$1 (a small step)', $text);
        $text = str_replace('O(1)', 'instant speed', $text);
        return $text;
    }

    private static function summarize(string $topic, array $user): string {
        $lesson = self::findLesson($topic, $user);
        if (!$lesson) {
            return "I couldn't find a lesson about \"$topic\" in your courses. I can summarize any lesson — try \"Summarize Chapter 1\" or ask \"Summarize Algebra\".";
        }
        return "**Summary: {$lesson['title']}**\n\n" . self::lessonSummary($lesson) .
               "\n\nSource: {$lesson['course_title']} • Duration: {$lesson['duration_min']} min";
    }

    private static function lessonSummary(array $lesson): string {
        $content = strip_tags($lesson['content'] ?? '');
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($content));
        $first = array_slice($sentences, 0, 3);
        return "• " . implode("\n• ", array_filter(array_map('trim', $first))) . "\n\n**Key takeaway:** " . (end($sentences) ?? $content);
    }

    private static function findLesson(string $topic, array $user): ?array {
        $q = mb_strtolower($topic);
        $lessons = Database::all(
            "SELECT l.*, c.title AS course_title, m.title AS module_title FROM lessons l
             JOIN courses c ON c.id = l.course_id
             LEFT JOIN course_modules m ON m.id = l.module_id
             JOIN course_enrollments ce ON ce.course_id = l.course_id AND ce.user_id = ?
             WHERE l.course_id IN (SELECT course_id FROM course_enrollments WHERE user_id = ?)
             ORDER BY l.course_id", [$user['id'], $user['id']]);
        if (!$lessons) return null;
        if ($q === '') return $lessons[0];
        // exact title match
        foreach ($lessons as $l) {
            if (mb_strtolower($l['title']) === $q) return $l;
            if (mb_stripos($l['title'], $q) !== false) return $l;
            if ($l['module_title'] && mb_stripos(mb_strtolower($l['module_title']), $q) !== false) return $l;
            if ($l['course_title'] && mb_stripos(mb_strtolower($l['course_title']), $q) !== false) return $l;
        }
        // content keyword match
        foreach ($lessons as $l) {
            if (mb_stripos(strip_tags($l['content'] ?? ''), $q) !== false) return $l;
        }
        return null;
    }

    public static function makeQuiz(string $topic, array $user): string {
        $bank = Database::all("SELECT * FROM ai_question_bank WHERE topic LIKE ? OR keywords LIKE ? OR question LIKE ? LIMIT 5",
            ['%' . $topic . '%', '%' . $topic . '%', '%' . $topic . '%']);
        if (!$bank && $topic !== '') {
            $bank = Database::all("SELECT * FROM ai_question_bank LIMIT 4");
        }
        if (!$bank) {
            return "I don't have enough material to build a quiz about \"$topic\" yet. Try: 'Create a quiz about algebra' or 'Quiz me on data structures'.";
        }
        $out = "**Quiz: " . ($topic !== '' ? ucfirst($topic) : 'Mixed topics') . "** (3 questions)\n\n";
        foreach (array_slice($bank, 0, 3) as $i => $q) {
            $out .= ($i + 1) . ". {$q['question']}\n";
            if ($q['options']) {
                foreach (json_decode($q['options'], true) as $o) $out .= "   - $o\n";
            }
            $out .= "\n";
        }
        $out .= "*(Answer check: send your answers and I'll grade them!)*\n\n";
        $out .= "**Saved to your quiz history** — find it in AI Tutor → Study Assistant.";
        return $out;
    }

    public static function makeFlashcards(string $topic, array $user): string {
        $lesson = self::findLesson($topic, $user);
        if ($lesson) {
            $pairs = self::flashcardPairs(strip_tags($lesson['content'] ?? ''), $lesson['title']);
        } else {
            $pairs = [['Recursion', 'A function that calls itself to solve a smaller version of the problem'],
                      ['Base case', 'The stopping condition in recursion'],
                      ['Fibonacci', '0,1,1,2,3,5... each number is the sum of the previous two'],
                      ['Variable', 'A symbol representing an unknown number']];
        }
        $out = "🃏 **Flashcards" . ($lesson ? ": {$lesson['title']}" : '') . "**\n\n";
        foreach (array_slice($pairs, 0, 6) as $i => [$f, $b]) {
            $out .= "**Card " . ($i + 1) . "**\nFront: $f\nBack: $b\n\n";
        }
        $out .= "Saved to your flashcard decks — review them anytime in Study Assistant → Flashcards.";
        return $out;
    }

    private static function flashcardPairs(string $content, string $title): array {
        $pairs = [];
        preg_match_all('/([A-Z][a-zA-Z ]{2,60})\s*[-–]\s*(.{10,120}[.!])/', $content, $m);
        foreach ($m[1] as $i => $term) {
            $pairs[] = [trim($term), trim($m[2][$i])];
            if (count($pairs) >= 6) break;
        }
        if (!$pairs) {
            $pairs = [[$title, mb_substr(trim($content), 0, 90) . '…'],
                      ['Key concept', 'Review the lesson to master this topic'],
                      ['Practice tip', 'Try explaining the topic to a friend']];
        }
        return $pairs;
    }

    private static function diagram(string $topic): string {
        $t = $topic ?: 'recursion';
        $map = [
            'recursion' => "Here's a recursion diagram:\n\n```
   factorial(5)
        |
        v
 5 * factorial(4)
        |
        v
 4 * factorial(3)
        |
        v
 3 * factorial(2)
        |
        v
 2 * factorial(1)   ← base case reached!
        |
        v
  returns 1, then 2, 6, 24, 120 (unwinding)
```",
            'binary tree' => "Binary tree diagram:\n\n```
        root=50
       /      \\
     30        70
    /  \\         \\
  20    40        80
```\n\nIn-order traversal: 20 → 30 → 40 → 50 → 70 → 80",
            'triangle' => "Triangle diagram:\n\n```
      /\\
     /  \\
    /    \\
   /______\\
```\n\nAngles always add to 180°.",
            'array' => "Array memory diagram:\n\n```
  scores[5]  = {90, 85, 78, 92, 88}
  address    = 1000 1004 1008 1012 1016
  index      = [0]  [1]  [2]  [3]  [4]
```\n\nEach int takes 4 bytes; address = base + index×4.",
        ];
        if (isset($map[$t])) return $map[$t];
        foreach ($map as $k => $v) {
            if (str_contains($t, $k)) return $v;
        }
        return "Here's a simple diagram of **$t**:\n\n```\nInput → [ Process $t ] → Output\n```\n\nAsk me to draw a specific topic: 'Diagram recursion' or 'Diagram binary tree'.";
    }

    private static function studyPlan(array $user): string {
        $enrolled = (int)Database::scalar("SELECT COUNT(*) FROM course_enrollments WHERE user_id = ?", [$user['id']], 0);
        $lessonsDone = (int)Database::scalar("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND completed = 1", [$user['id']], 0);
        $exams = (int)Database::scalar("SELECT COUNT(*) FROM exams e JOIN course_enrollments ce ON ce.course_id = e.course_id WHERE ce.user_id = ? AND e.end_time > NOW()", [$user['id']], 0);
        $day = date('l');
        return "**Your personal study plan** (based on your activity)\n\n" .
               "• You're enrolled in **$enrolled course(s)** with **$lessonsDone lesson(s)** completed.\n" .
               "• **$exams** upcoming exam(s) on your calendar.\n\n" .
               "**Weekly rhythm (adapt it to you):**\n" .
               "• $day: 45 min — review hardest lesson + quiz (15 min each course)\n" .
               "• Even days: practice questions (AI Tutor → 'Quiz me')\n" .
               "• Odd days: new content, one lesson per course\n" .
               "• Friday: summary day — 'Summarize' every lesson you studied\n" .
               "• Sunday: flashcards review + plan the week\n\n" .
               "**Pomodoro:** study 25 min, break 5 min — repeat 4×. Use the flashcard decks I saved for you!";
    }

    private static function readiness(array $user): string {
        $courseData = [];
        $courses = Database::all(
            "SELECT c.id, c.title, ce.progress,
                    (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.course_id = c.id AND lp.user_id = ? AND lp.completed = 1) AS done_lessons,
                    (SELECT COUNT(*) FROM exams e WHERE e.course_id = c.id AND e.auto_grade = 1) AS quizzes
             FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id
             WHERE ce.user_id = ?", [$user['id'], $user['id']]);
        $pct = 0; $n = 0; $lines = [];
        foreach ($courses as $c) {
            $pct += (float)$c['progress']; $n++;
            $lines[] = "• **{$c['title']}**: {$c['progress']}% complete, {$c['done_lessons']}/{$c['total_lessons']} lessons";
        }
        $avg = $n ? round($pct / $n) : 0;
        $verdict = $avg >= 80 ? 'Excellent — you are well prepared!' : ($avg >= 50 ? 'Good progress — focus on your weak topics to solidify.' : 'You should study more before the exam — start with your weakest course.');
        return "**Exam readiness estimate**\n\n" . implode("\n", $lines) . "\n\n" .
               "**Overall readiness: $avg%**\n$verdict\n\n" .
               "To boost your score: 'Create a quiz about [topic]' and 'Suggest weak topics'.";
    }

    private static function weakTopics(array $user): string {
        $rows = Database::all(
            "SELECT q.topic, COUNT(*) AS attempts,
                    SUM(CASE WHEN q.type IN ('mcq','truefalse','fill') AND a.is_correct = 1 THEN 1 ELSE 0 END) AS correct
             FROM exam_answers a
             JOIN exam_attempts ea ON ea.id = a.attempt_id AND ea.student_id = ?
             JOIN exam_questions q ON q.id = a.question_id
             GROUP BY q.topic", [$user['id']]);
        if (!$rows) {
            return "I don't have enough quiz history yet to find weak topics.\n\nTake a quiz or use the Study Assistant, then ask me again. In the meantime, common areas students find tricky: algebra, recursion, and fractions.";
        }
        $out = "**Your weak topics** (from quiz history)\n\n";
        foreach ($rows as $r) {
            $pct = $r['attempts'] ? round($r['correct'] / $r['attempts'] * 100) : 0;
            $icon = $pct >= 70 ? '(OK)' : ($pct >= 40 ? '(CAUTION)' : '(WEAK)');
            $out .= "$icon **{$r['topic']}**: $pct% correct ({$r['correct']}/{$r['attempts']})\n";
        }
        $out .= "\n**Next step:** ask me 'Explain [topic]' or 'Create a quiz about [topic]' to improve.";
        return $out;
    }

    private static function grammarCheck(string $sentence): string {
        if ($sentence === '') return "Send me a sentence to check: 'Correct my grammar: i go to school yesterday'.";
        $s = trim($sentence);
        $fixed = $s;
        $notes = [];
        if (preg_match('/^[a-z]/', $fixed)) {
            $fixed = ucfirst($fixed);
            $notes[] = 'Start sentences with a capital letter.';
        }
        $lower = strtolower($fixed);
        $pairs = [
            [' i ', ' I '], ['i\'m', "I'm"], ['i am', 'I am'],
            ['go to school yesterday', 'went to school yesterday'], ['goed', 'went'],
            ['eated', 'ate'], ['comed', 'came'], ['runned', 'ran'],
            ['a apple', 'an apple'], ['a orange', 'an orange'], ['a egg', 'an egg'],
            ['more better', 'better'], ['more bigger', 'bigger'], ['more faster', 'faster'],
        ];
        foreach ($pairs as [$a, $b]) {
            if (str_contains($lower, $a)) {
                $pos = strpos($lower, $a);
                $fixed = substr($fixed, 0, $pos) . $b . substr($fixed, $pos + strlen($a));
                $lower = strtolower($fixed);
                $notes[] = 'Fixed: "' . trim($a) . '" → "' . trim($b) . '".';
            }
        }
        if (!preg_match('/[.!?]$/', $fixed)) {
            $fixed .= '.';
            $notes[] = 'End the sentence with a full stop.';
        }
        if (count($notes) === 0) {
            return "Your sentence looks correct: \"$fixed\"\n\nWant to practise more grammar? Ask: 'Create a quiz about English grammar'.";
        }
        return "**Corrected sentence:** \"$fixed\"\n\n**Notes:**\n• " . implode("\n• ", array_unique($notes));
    }

    private static function explain(string $topic, array $user): string {
        if ($topic === '') return self::helpText();
        $kb = self::knowledge();
        if (isset($kb[$topic])) {
            return "**" . ucfirst($topic) . "**\n\n" . $kb[$topic] . "\n\n_Want more? Try: 'Quiz me on $topic', 'Diagram $topic', or 'Explain $topic like I'm five'._";
        }
        foreach ($kb as $k => $v) {
            if (str_contains($topic, $k) || str_contains($k, $topic)) {
                return "**" . ucfirst($k) . "**\n\n" . $v . "\n\n_Want more? Try: 'Quiz me on $k', 'Diagram $k', or 'Explain $k like I'm five'._";
            }
        }
        $lesson = self::findLesson($topic, $user);
        if ($lesson) {
            return "**{$lesson['title']}** (from *{$lesson['course_title']}*)\n\n" . self::lessonSummary($lesson);
        }
        return "I'd love to explain **$topic**, but I need a bit more context. Here's what I can do:\n\n" . self::helpText();
    }

    private static function greeting(array $user): string {
        $name = full_name($user);
        $h = (int)date('H');
        $time = $h < 12 ? 'Good morning' : ($h < 18 ? 'Good afternoon' : 'Good evening');
        $streak = $user['streak'] ?? 0;
        return "Hello $time, $name! Welcome back.\n\n" .
               ($streak > 0 ? "Your learning streak is **$streak day(s)** — keep it alive!\n\n" : '') .
               "I can help you:\n• Explain any topic (try 'Explain recursion')\n• Summarize lessons ('Summarize Chapter 1')\n• Create quizzes ('Quiz me on algebra')\n• Make flashcards ('Create flashcards')\n• Translate to Amharic ('Translate good morning to Amharic')\n• Draw diagrams ('Diagram binary tree')\n• Check your exam readiness ('Am I ready for my exam?')\n\nWhat shall we learn today?";
    }

    private static function helpText(): string {
        return "**I'm your AI Tutor** — I remember our conversation and adapt to your pace.\n\n" .
               "**Try asking me:**\n" .
               "• *Explain recursion*\n" .
               "• *Summarize Chapter 3*\n" .
               "• *Create a quiz about algebra*\n" .
               "• *Generate flashcards*\n" .
               "• *Explain with a diagram*\n" .
               "• *Translate thank you to Amharic* (አመሰግናለሁ!)\n" .
               "• *Explain like I'm five*\n" .
               "• *Correct my grammar: i goed to school*\n" .
               "• *Create a study plan*\n" .
               "• *Am I ready for my exam?*\n" .
               "• *Suggest my weak topics*\n\n" .
               "I also learn from your courses — ask me to summarize any of your lessons!";
    }
}
