const { Client, GatewayIntentBits, Partials, PermissionsBitField } = require('discord.js');
const client = new Client({
    intents: [GatewayIntentBits.Guilds, GatewayIntentBits.GuildMessages, GatewayIntentBits.MessageContent],
    partials: [Partials.Message, Partials.Channel]
});

// Token của bot từ Discord Developer Portal
const TOKEN = 'MTI5ODA2MDUxNDQ0OTk0ODc5Mw.GimuXk.5uw2AYuQ-bHwTt3Xm6qH740OhjOOssLHShGWDU' // Thay bằng token của bạn

// Các URL cần chặn
const blockedURLs = [
    'https://gateway.platoboost.com/',
    'https://pandadevelopment.net/'
];

// Sự kiện 'ready' khi bot đã kết nối thành công
client.once('ready', () => {
    console.log(`Bot đã kết nối thành công với tên: ${client.user.tag}`);
});

client.on('messageCreate', async (message) => {
    // Nếu tin nhắn từ bot hoặc không chứa URL cần chặn, bỏ qua
    if (message.author.bot) return;

    // Lấy thành viên từ guild
    const member = await message.guild.members.fetch(message.author.id);

    // Kiểm tra xem thành viên có quyền quản lý tin nhắn không
    const isManager = member.permissions.has(PermissionsBitField.Flags.ManageMessages);

    // Kiểm tra xem tin nhắn có chứa bất kỳ URL nào bị chặn không
    const containsBlockedURL = blockedURLs.some(url => message.content.includes(url));

    if (containsBlockedURL && !isManager) {
        // Xóa tin nhắn chứa URL bị chặn nếu người dùng không phải là quản lý
        await message.delete();
        console.log(`Đã xóa tin nhắn chứa URL bị chặn từ người dùng: ${message.author.tag}`);

        // Gửi tin nhắn mention người gửi và hẹn giờ để tự động xóa sau 5 giây
        const botMessage = await message.channel.send({
            content: `Link bypass không được hỗ trợ.`,
            allowedMentions: { users: [message.author.id] }
        });

        // Đặt thời gian 5 giây (5000 milliseconds) để tự động xóa tin nhắn của bot
        setTimeout(() => {
            botMessage.delete().catch(console.error);  // Xóa tin nhắn và bắt lỗi nếu có
        }, 5000);  // 5 giây
    }
});

// Kết nối bot
client.login(TOKEN);
