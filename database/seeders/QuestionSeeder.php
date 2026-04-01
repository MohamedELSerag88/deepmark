<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
	public function run(): void
	{
		$items = [
			[
				'question_en' => 'What’s your brand’s main point‑of‑view or core belief?',
				'question_ar' => 'ما هو الموقف أو الاعتقاد الأساسي لعلامتك؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Strong brands stand for something – what’s your big “why”?',
				'description_ar' => 'العلامات القوية تؤمن بشيء ما – ما هو سبب وجودك؟',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'We believe great design should be accessible, human, and useful.',
				'resources' => [
					[ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
					[ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
				],
			],
			[
				'question_en' => 'Who is your primary audience and what do they value most?',
				'question_ar' => 'من هو جمهورك الأساسي وما الذي يقدرونه أكثر؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Describe the people you serve and their top priorities.',
				'description_ar' => 'صف الأشخاص الذين تخدمهم وأهم أولوياتهم.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'Ambitious founders who value clarity, speed, and practical outcomes.',
                'resources' => [
                    [ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
                    [ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
                ],
			],
			[
				'question_en' => 'What transformation should customers feel after engaging with your brand?',
				'question_ar' => 'ما التغيير الذي يجب أن يشعر به العملاء بعد تفاعلهم مع علامتك؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'State the before and after in emotional and practical terms.',
				'description_ar' => 'اذكر الحالة قبل وبعد بشكل عاطفي وعملي.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'From overwhelmed to confident and action‑ready.',
                'resources' => [
                    [ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
                    [ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
                ],
			],
			[
				'question_en' => 'How would you describe your brand’s voice and tone?',
				'question_ar' => 'كيف تصف صوت ونبرة علامتك؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'List a few adjectives and sample phrases that sound on‑brand.',
				'description_ar' => 'اذكر بعض الصفات وعبارات نموذجية تعبّر عن صوت العلامة.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'Friendly, concise, helpful – “Let’s get you moving in minutes.”',
                'resources' => [
                    [ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
                    [ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
                ],
			],
			[
				'question_en' => 'Who are your top competitors and how are you different?',
				'question_ar' => 'من هم أبرز منافسيك وكيف تختلف عنهم؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Name 2–3 competitors and the key differentiators that set you apart.',
				'description_ar' => 'اذكر 2–3 منافسين والعوامل الرئيسية التي تميّزك عنهم.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'We offer guided workflows and live onboarding; they’re fully self‑serve.',
                'resources' => [
                    [ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
                    [ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
                ],
			],
			[
				'question_en' => 'What visual attributes should your brand consistently express?',
				'question_ar' => 'ما السمات البصرية التي يجب أن تعبّر عنها علامتك باستمرار؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Think colors, typography, shapes, and overall mood.',
				'description_ar' => 'فكّر بالألوان والخطوط والأشكال والمزاج العام.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'Warm neutrals, rounded typography, generous white space, calming imagery.',
                'resources' => [
                    [ 'url' => 'https://www.google.com/', 'text' => 'Customers connect with personalities, not services.' ],
                    [ 'url' => 'https://www.google.com/', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
                ],
			],
			[
				'question_en' => 'What single action do you most want visitors to take?',
				'question_ar' => 'ما الإجراء الأساسي الذي تريد من الزائر القيام به؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'This defines your primary call‑to‑action and success metric.',
				'description_ar' => 'هذا يحدد الدعوة الأساسية لاتخاذ إجراء ومقياس النجاح.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'Start free trial (creates an account and starts onboarding).',
				'resources' => null,
			],
		];

		foreach ($items as $item) {
			Question::updateOrCreate(
				['question_en' => $item['question_en'], 'question_ar' => $item['question_ar']],
				[
					'question_type' => $item['question_type'],
					'answers' => $item['answers'],
					'description_en' => $item['description_en'] ?? null,
					'description_ar' => $item['description_ar'] ?? null,
					'video_url' => $item['video_url'] ?? null,
					'video_path' => $item['video_path'] ?? null,
					'image_url' => $item['image_url'] ?? null,
					'example_answer' => $item['example_answer'] ?? null,
					'resources' => $item['resources'] ?? null,
				]
			);
		}
	}
}


