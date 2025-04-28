<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $techPosts = [
            [
                'title' => 'Trí tuệ nhân tạo và tương lai của ngành công nghiệp tự động hóa',
                'description' => 'Khám phá cách trí tuệ nhân tạo đang định hình lại tương lai của ngành công nghiệp tự động hóa, từ nhà máy thông minh đến quy trình sản xuất hoàn toàn tự động.',
                'content' => 'Trí tuệ nhân tạo (AI) đang mang đến cuộc cách mạng lớn trong ngành công nghiệp tự động hóa. Các doanh nghiệp sản xuất hiện nay đang ứng dụng AI vào quy trình sản xuất để tối ưu hóa hiệu suất, giảm thiểu lỗi và tiết kiệm chi phí. 

Với khả năng học hỏi từ dữ liệu và đưa ra quyết định tự động, AI cho phép máy móc tự điều chỉnh và tối ưu hóa quy trình sản xuất mà không cần sự can thiệp của con người. Điều này không chỉ giúp tăng năng suất mà còn nâng cao chất lượng sản phẩm.

Các nhà máy thông minh (Smart Factory) đang trở thành xu hướng phát triển của ngành công nghiệp 4.0. Tại đây, mọi thiết bị được kết nối với nhau thông qua Internet vạn vật (IoT), cho phép AI phân tích dữ liệu theo thời gian thực và đưa ra các quyết định tối ưu.

Ngoài ra, AI còn giúp dự đoán các lỗi có thể xảy ra trong quá trình sản xuất, từ đó đưa ra các biện pháp phòng ngừa kịp thời. Điều này không chỉ giúp tiết kiệm chi phí sửa chữa mà còn đảm bảo quy trình sản xuất liên tục, không bị gián đoạn.

Tuy nhiên, việc áp dụng AI vào ngành công nghiệp tự động hóa cũng đặt ra nhiều thách thức về đào tạo nguồn nhân lực, bảo mật dữ liệu và đầu tư cơ sở hạ tầng. Doanh nghiệp cần có chiến lược phù hợp để tận dụng tối đa lợi ích của AI trong thời đại số.',
            ],
            [
                'title' => 'Blockchain và cách mạng hóa ngành tài chính số',
                'description' => 'Tìm hiểu về công nghệ blockchain đang thay đổi cách chúng ta hiểu về tiền tệ, giao dịch và bảo mật trong thế giới tài chính hiện đại.',
                'content' => 'Blockchain đang trở thành công nghệ đột phá trong ngành tài chính số, mang lại cách tiếp cận hoàn toàn mới về giao dịch và bảo mật. Với tính chất phi tập trung và minh bạch, blockchain đã làm thay đổi cách chúng ta hiểu về tiền tệ và các giao dịch tài chính.

Khác với hệ thống tài chính truyền thống phụ thuộc vào các trung gian như ngân hàng, blockchain cho phép các giao dịch được thực hiện trực tiếp giữa các bên tham gia mà không cần đến bên thứ ba. Điều này không chỉ giúp giảm chi phí giao dịch mà còn tăng tốc độ xử lý.

Smart contract (hợp đồng thông minh) là một trong những ứng dụng quan trọng của blockchain trong lĩnh vực tài chính. Đây là các đoạn mã tự động thực thi khi các điều kiện được đáp ứng, loại bỏ nhu cầu về trung gian và giảm nguy cơ gian lận.

Các ngân hàng và tổ chức tài chính lớn trên toàn cầu đang đầu tư mạnh vào nghiên cứu và phát triển các ứng dụng blockchain. Từ hệ thống thanh toán xuyên biên giới đến quản lý danh tính số, blockchain đang mở ra nhiều cơ hội mới cho ngành tài chính.

Tuy nhiên, việc áp dụng rộng rãi blockchain trong ngành tài chính vẫn phải đối mặt với nhiều thách thức về quy định pháp lý, tiêu chuẩn hóa và khả năng mở rộng. Sự phát triển của công nghệ này sẽ phụ thuộc vào khả năng giải quyết các vấn đề trên trong tương lai gần.',
            ],
            [
                'title' => 'Thực tế ảo (VR) và thực tế tăng cường (AR) trong giáo dục',
                'description' => 'Phân tích tiềm năng và ứng dụng thực tế của công nghệ VR và AR trong việc cách mạng hóa phương pháp giảng dạy và học tập.',
                'content' => 'Thực tế ảo (Virtual Reality - VR) và thực tế tăng cường (Augmented Reality - AR) đang dần thay đổi bộ mặt của nền giáo dục hiện đại. Hai công nghệ này mang đến trải nghiệm học tập sống động và tương tác, giúp người học tiếp thu kiến thức một cách hiệu quả hơn.

VR cho phép người học được đắm mình trong môi trường học tập ba chiều hoàn toàn ảo. Sinh viên y khoa có thể thực hành phẫu thuật ảo trước khi bước vào phòng mổ thực tế, sinh viên kiến trúc có thể đi dạo trong các công trình thiết kế của mình, hay học sinh địa lý có thể tham quan các di tích lịch sử trên toàn thế giới mà không cần rời khỏi lớp học.

Trong khi đó, AR kết hợp yếu tố ảo với thế giới thực, tạo ra trải nghiệm học tập tăng cường. Ví dụ, học sinh có thể sử dụng ứng dụng AR để quét trang sách giáo khoa và xem hình ảnh 3D minh họa, video giải thích hoặc nội dung tương tác bổ sung.

Nhiều nghiên cứu đã chỉ ra rằng việc học thông qua trải nghiệm với VR và AR giúp tăng khả năng ghi nhớ và hiểu bài. Những công nghệ này đặc biệt hữu ích cho việc học các khái niệm trừu tượng hoặc phức tạp, khi mà phương pháp truyền thống gặp nhiều hạn chế.

Tuy nhiên, việc triển khai VR và AR trong giáo dục vẫn còn nhiều thách thức về chi phí đầu tư thiết bị, phát triển nội dung và đào tạo giáo viên. Nhưng với sự phát triển nhanh chóng của công nghệ và giá thành ngày càng giảm, tương lai của VR và AR trong giáo dục là rất triển vọng.',
            ],
            [
                'title' => 'Internet vạn vật (IoT) và những thay đổi trong cuộc sống đô thị',
                'description' => 'Tìm hiểu cách Internet vạn vật đang biến đổi các thành phố thành "thành phố thông minh" và cải thiện chất lượng cuộc sống đô thị.',
                'content' => 'Internet vạn vật (Internet of Things - IoT) đang dần định hình lại bộ mặt của các đô thị hiện đại, biến những thành phố thông thường thành "thành phố thông minh" (Smart City) với hệ thống quản lý hiệu quả và bền vững hơn.

Với IoT, các thiết bị, cảm biến và hệ thống trong thành phố được kết nối với nhau, thu thập và phân tích dữ liệu theo thời gian thực. Điều này cho phép chính quyền thành phố đưa ra các quyết định dựa trên dữ liệu, tối ưu hóa việc sử dụng tài nguyên và cải thiện chất lượng cuộc sống cho người dân.

Hệ thống giao thông thông minh là một trong những ứng dụng nổi bật của IoT trong đô thị. Các cảm biến được lắp đặt tại đèn giao thông, bãi đỗ xe và trên các tuyến đường chính có thể giúp quản lý luồng giao thông hiệu quả, giảm tắc nghẽn và ô nhiễm.

Trong lĩnh vực năng lượng, IoT giúp xây dựng lưới điện thông minh (Smart Grid) có khả năng tự điều chỉnh và tối ưu hóa việc sử dụng năng lượng. Các thiết bị trong nhà như đèn, điều hòa, tủ lạnh được kết nối với nhau và có thể tự động điều chỉnh mức tiêu thụ điện dựa trên nhu cầu thực tế.

Quản lý chất thải và tài nguyên nước cũng được cải thiện đáng kể nhờ IoT. Các thùng rác thông minh có thể thông báo khi đầy và tối ưu hóa lộ trình thu gom, trong khi hệ thống cấp nước thông minh có thể phát hiện rò rỉ và giảm lãng phí.

Tuy nhiên, việc triển khai IoT trong đô thị cũng đặt ra nhiều thách thức về bảo mật dữ liệu, quyền riêng tư và chi phí đầu tư. Các thành phố cần có chiến lược toàn diện để tận dụng tối đa tiềm năng của IoT mà vẫn đảm bảo an toàn cho người dân.',
            ],
            [
                'title' => 'Điện toán đám mây và tương lai của việc lưu trữ dữ liệu',
                'description' => 'Khám phá cách điện toán đám mây đang thay đổi cách doanh nghiệp và cá nhân lưu trữ, xử lý và bảo vệ dữ liệu trong kỷ nguyên số.',
                'content' => 'Điện toán đám mây (Cloud Computing) đã và đang mang lại cuộc cách mạng trong cách chúng ta lưu trữ, xử lý và bảo vệ dữ liệu. Từ doanh nghiệp lớn đến người dùng cá nhân, ai cũng đang hưởng lợi từ sự linh hoạt và hiệu quả mà công nghệ này mang lại.

Không còn phải đầu tư vào hạ tầng máy chủ đắt tiền, các doanh nghiệp giờ đây có thể thuê dịch vụ đám mây theo nhu cầu sử dụng. Điều này không chỉ giúp tiết kiệm chi phí đầu tư ban đầu mà còn cho phép mở rộng quy mô linh hoạt khi nhu cầu tăng lên.

Các mô hình dịch vụ đám mây như Infrastructure as a Service (IaaS), Platform as a Service (PaaS) và Software as a Service (SaaS) cung cấp nhiều lựa chọn cho người dùng tùy theo nhu cầu cụ thể. Từ việc thuê máy chủ ảo đến sử dụng các ứng dụng phần mềm trực tuyến, tất cả đều có thể thực hiện thông qua đám mây.

Bảo mật và khả năng phục hồi dữ liệu cũng là những ưu điểm quan trọng của điện toán đám mây. Các nhà cung cấp dịch vụ đám mây hàng đầu như AWS, Microsoft Azure và Google Cloud đầu tư rất lớn vào hệ thống bảo mật nhiều lớp và các giải pháp sao lưu dữ liệu tự động.

Edge Computing (Điện toán biên) đang nổi lên như một xu hướng mới bên cạnh Cloud Computing, cho phép xử lý dữ liệu gần với nguồn tạo ra dữ liệu hơn. Điều này đặc biệt quan trọng đối với các ứng dụng yêu cầu độ trễ thấp như xe tự lái hoặc hệ thống y tế từ xa.

Tuy nhiên, việc chuyển đổi sang đám mây cũng đặt ra những thách thức về quyền riêng tư dữ liệu, tuân thủ quy định và phụ thuộc vào nhà cung cấp. Doanh nghiệp cần có chiến lược đám mây toàn diện để tận dụng tối đa lợi ích mà vẫn giảm thiểu rủi ro liên quan.',
            ],
        ];

        $user = User::query()->where('email', 'coursemely@gmail.com')->first();

        foreach ($techPosts as $post) {
            Post::create([
                'user_id' => $user->id,
                'category_id' => rand(1, 5),
                'title' => $post['title'],
                'slug' => Str::slug($post['title']) . '-' . Str::uuid(),
                'description' => $post['description'],
                'content' => $post['content'],
                'status' => 'published',
                'views' => rand(100, 5000),
                'is_hot' => 1,
                'published_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
